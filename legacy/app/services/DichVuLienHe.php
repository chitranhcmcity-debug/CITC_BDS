<?php
/**
 * ContactService – Xử lý luồng nghiệp vụ gửi liên hệ, upload file đính kèm, phân công AI và đồng bộ CRM.
 */
class ContactService
{
    private ContactRepository $contactRepo;
    private ContactValidation $validation;
    private CRMService $crmSvc;
    private MailService $mailSvc;

    public function __construct()
    {
        require_once APP_ROOT . '/app/repositories/ContactRepository.php';
        require_once APP_ROOT . '/app/validation/ContactValidation.php';
        require_once APP_ROOT . '/app/services/DichVuCRM.php';
        require_once APP_ROOT . '/app/services/DichVuThuDienTu.php';

        $this->contactRepo = new ContactRepository();
        $this->validation  = new ContactValidation();
        $this->crmSvc      = new CRMService();
        $this->mailSvc     = new MailService();
    }

    /**
     * Tạo yêu cầu liên hệ mới.
     * @param array $input Dữ liệu đầu vào ($_POST)
     * @param array $files Mảng file đính kèm ($_FILES)
     * @return array Kết quả xử lý ['success' => bool, 'errors' => array, 'contact_id' => int]
     */
    public function createContact(array $input, array $files = []): array
    {
        // 1. Xác thực dữ liệu
        $errors = $this->validation->validate($input);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // 2. Thu thập siêu dữ liệu người gửi (IP, Browser, Device)
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $browser = $this->parseBrowser($userAgent);
        $device = $this->parseDevice($userAgent);

        // 3. AI Tự động phân loại yêu cầu nếu người dùng chọn 'khac' hoặc trống
        $type = $input['type'] ?? 'khac';
        $content = $input['content'] ?? '';
        if (($type === 'khac' || empty($type)) && !empty($content)) {
            $aiType = $this->crmSvc->aiClassifyRequest($content);
            if ($aiType) {
                $type = $aiType;
            }
        }

        // 4. Chuẩn bị lưu database
        $contactData = [
            'fullname'   => trim($input['fullname']),
            'phone'      => trim($input['phone']),
            'email'      => !empty($input['email']) ? trim($input['email']) : null,
            'subject'    => !empty($input['subject']) ? trim($input['subject']) : null,
            'content'    => !empty($content) ? trim($content) : null,
            'type'       => $type,
            'ip_address' => $ipAddress,
            'browser'    => $browser,
            'device'     => $device
        ];

        // 5. Lưu vào Database
        $contactId = $this->contactRepo->create($contactData);

        // 6. Xử lý file đính kèm (nếu có)
        if (!empty($files['attachments'])) {
            $this->handleAttachments($contactId, $files['attachments']);
        }

        // 7. AI Tự động phân công chuyên viên CSKH
        $assignedAdminId = $this->autoAssignAdmin($type);
        if ($assignedAdminId) {
            $this->contactRepo->update($contactId, ['assigned_admin' => $assignedAdminId]);
        }

        // Lấy thông tin liên hệ đầy đủ sau khi đã cập nhật admin
        $fullContact = $this->contactRepo->findById($contactId);
        if ($fullContact) {
            // 8. Tự động gửi Email xác nhận cho Khách hàng
            if (!empty($fullContact['email'])) {
                $this->mailSvc->sendContactConfirmation($fullContact);
            }

            // 9. Gửi Email thông báo phân công cho Chuyên viên (Admin)
            if (!empty($fullContact['admin_email'])) {
                $this->mailSvc->sendContactAdminNotification(
                    $fullContact, 
                    $fullContact['admin_email'], 
                    $fullContact['admin_name'] ?: 'Chuyên viên'
                );
            }

            // 10. Chạy CRM Workflow (Tạo Notification hệ thống + Telegram cảnh báo)
            $this->crmSvc->processNewContact($fullContact);
        }

        return [
            'success' => true,
            'contact_id' => $contactId,
            'message' => 'Cảm ơn bạn đã gửi yêu cầu. Chúng tôi sẽ liên hệ trong thời gian sớm nhất.'
        ];
    }

    /**
     * Tự động gán quyền xử lý liên hệ cho Admin phù hợp.
     */
    private function autoAssignAdmin(string $type): ?int
    {
        try {
            $db = new Database();
            // Lấy danh sách admin hoạt động
            $db->query("SELECT id, ten FROM nguoi_dung WHERE ma_vai_tro = 1 AND trang_thai = 'hoat_dong'");
            $admins = $db->resultSet();

            if (empty($admins)) {
                return null;
            }

            // Sử dụng AI phân công
            $adminsArray = array_map(fn($a) => ['id' => (int)$a->id, 'ten' => $a->ten], $admins);
            return $this->crmSvc->aiAutoAssignCSKH($type, $adminsArray);
        } catch (Exception $e) {
            error_log("ContactService AutoAssignAdmin Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Xử lý di chuyển file đính kèm và lưu vào DB.
     */
    private function handleAttachments(int $contactId, array $filesInput): void
    {
        $uploadDir = UPLOAD_ROOT_DIR . '/contacts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Chuẩn hóa cấu trúc $_FILES đa file
        $files = [];
        if (is_array($filesInput['name'])) {
            for ($i = 0; $i < count($filesInput['name']); $i++) {
                if ($filesInput['error'][$i] === UPLOAD_ERR_OK) {
                    $files[] = [
                        'name'     => $filesInput['name'][$i],
                        'type'     => $filesInput['type'][$i],
                        'tmp_name' => $filesInput['tmp_name'][$i],
                        'error'    => $filesInput['error'][$i],
                        'size'     => $filesInput['size'][$i],
                    ];
                }
            }
        } else {
            if ($filesInput['error'] === UPLOAD_ERR_OK) {
                $files[] = $filesInput;
            }
        }

        foreach ($files as $file) {
            // Xác thực file đính kèm
            $fileErr = $this->validation->validateFile($file);
            if ($fileErr) {
                error_log("Upload File Error: " . $fileErr);
                continue;
            }

            $originalName = $file['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $randomName = 'contact_attachment_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destPath = $uploadDir . $randomName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                // Lưu thông tin vào CSDL
                $this->contactRepo->createAttachment([
                    'contact_id' => $contactId,
                    'file_name'  => $originalName,
                    'file_path'  => 'contacts/' . $randomName,
                    'file_type'  => $file['type'],
                    'file_size'  => $file['size']
                ]);
            }
        }
    }

    /**
     * Trích xuất thông tin trình duyệt từ User Agent.
     */
    private function parseBrowser(string $userAgent): string
    {
        if (empty($userAgent)) return 'N/A';
        if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) return 'Internet Explorer';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Opera/i', $userAgent)) return 'Opera';
        if (preg_match('/Netscape/i', $userAgent)) return 'Netscape';
        return 'N/A';
    }

    /**
     * Trích xuất loại thiết bị từ User Agent.
     */
    private function parseDevice(string $userAgent): string
    {
        if (empty($userAgent)) return 'Desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) return 'Tablet';
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) return 'Mobile';
        return 'Desktop';
    }
}
