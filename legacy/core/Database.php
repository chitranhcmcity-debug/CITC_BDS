<?php
/**
 * Lớp Database - Quản lý kết nối và thao tác cơ sở dữ liệu
 * Sử dụng PDO (PHP Data Objects) để kết nối MySQL an toàn.
 * Hỗ trợ Prepared Statements để chống SQL Injection.
 */
class Database
{
    private string $host    = DB_HOST;
    private string $user    = DB_USER;
    private string $pass    = DB_PASS;
    private string $dbname  = DB_NAME;
    private string $charset = DB_CHARSET;

    /** Mot ket noi dung chung de transaction bao phu duoc nhieu Model. */
    private static ?\PDO $sharedConnection = null;

    /** @var \PDO Đối tượng kết nối PDO */
    private \PDO $dbh;

    /** @var \PDOStatement|null Câu lệnh SQL đã được chuẩn bị */
    private ?\PDOStatement $stmt = null;

    /** SQL gần nhất, chỉ dùng để chẩn đoán lỗi; không chứa giá trị bind nhạy cảm. */
    private string $lastQuery = '';

    // ==========================================
    // KHỞI TẠO KẾT NỐI
    // ==========================================

    /**
     * Khoi tao ket noi toi MySQL thong qua PDO.
     * Cau hinh lay tu cac hang so DB_* dinh nghia trong config/config.php.
     */
    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Nem ngoai le khi loi
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,    // Ket qua tra ve kieu Object
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            if (self::$sharedConnection === null) {
                self::$sharedConnection = new PDO($dsn, $this->user, $this->pass, $options);
            }
            $this->dbh = self::$sharedConnection;
        } catch (\PDOException $e) {
            // Chi hien thi loi tren moi truong dev, tren production nen ghi log
            error_log('DB Connection Error: ' . $e->getMessage());
            die('Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
        }
    }

    // ==========================================
    // CHUẨN BỊ VÀ THỰC THI CÂU LỆNH SQL
    // ==========================================

    /**
     * Chuan bi (prepare) cau lenh SQL de thuc thi.
     *
     * @param string $sql Cau lenh SQL co the co tham so (:param)
     */
    public function query(string $sql): void
    {
        $this->lastQuery = $sql;
        $this->stmt = $this->dbh->prepare($sql);
    }

    /**
     * Gan gia tri vao tham so trong cau lenh SQL da chuan bi.
     * Tu dong xac dinh kieu du lieu (int, bool, null, string).
     *
     * @param string     $param Ten tham so (vi du: ':id')
     * @param mixed      $value Gia tri can gan
     * @param int|null   $type  Kieu du lieu PDO (tu dong xac dinh neu khong truyen)
     */
    public function bind(string $param, mixed $value, ?int $type = null): void
    {
        if (is_null($type)) {
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Thuc thi cau lenh SQL da chuan bi.
     *
     * @return bool True neu thanh cong, False neu that bai
     */
    public function execute(): bool
    {
        try {
            return $this->stmt->execute();
        } catch (\PDOException $e) {
            $query = preg_replace('/\s+/', ' ', trim($this->lastQuery));
            error_log('DB Execute Error: ' . $e->getMessage() . ' | SQL: ' . $query);
            // Khong de transaction tiep tuc va commit du lieu dang do.
            if ($this->dbh->inTransaction()) {
                $this->dbh->rollBack();
            }
            return false;
        }
    }

    // ==========================================
    // LẤY KẾT QUẢ
    // ==========================================

    /**
     * Lay nhieu hang ket qua (dung cho SELECT nhieu ban ghi).
     *
     * @return array Mang cac doi tuong ket qua
     */
    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Lay mot hang ket qua duy nhat (dung cho SELECT 1 ban ghi).
     *
     * @return object|false Doi tuong ban ghi hoac false neu khong tim thay
     */
    public function single(): object|false
    {
        $this->execute();
        return $this->stmt ? $this->stmt->fetch() : false;
    }

    /**
     * Lay gia tri cua cot dau tien trong dong dau tien.
     */
    public function singleColumn(): mixed
    {
        $this->execute();
        return $this->stmt ? $this->stmt->fetchColumn() : null;
    }
    /**
     * Dem so hang bi anh huong sau lenh INSERT/UPDATE/DELETE.
     *
     * @return int So hang bi tac dong
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Lay ID cua ban ghi vua duoc them vao (sau INSERT).
     *
     * @return string ID cua ban ghi moi nhat
     */
    public function lastInsertId(): string
    {
        return $this->dbh->lastInsertId();
    }

    // ==========================================
    // DATABASE TRANSACTIONS (XỬ LÝ GIAO DỊCH TÀI CHÍNH)
    // ==========================================

    /**
     * Bat dau mot giao dich (GiaoDich).
     * Tat ca cac truy van tu thoi diem nay se khong duoc luu vao DB cho den khi goi commit().
     */
    public function beginTransaction(): bool
    {
        return $this->dbh->inTransaction() || $this->dbh->beginTransaction();
    }

    /**
     * Xac nhan luu (Commit) tat ca thay doi cua giao dich hien tai vao Database.
     */
    public function commit(): bool
    {
        return $this->dbh->inTransaction() ? $this->dbh->commit() : false;
    }

    /**
     * Hoan tac (Rollback) toan bo thay doi cua giao dich neu co loi xay ra.
     */
    public function rollBack(): bool
    {
        return $this->dbh->inTransaction() ? $this->dbh->rollBack() : false;
    }

    public function inTransaction(): bool
    {
        return $this->dbh->inTransaction();
    }
}
