<?php
/**
 * Component hiển thị Google Maps / OpenStreetMap địa chỉ văn phòng.
 */
$googleMapsKey = env('GOOGLE_MAPS_API_KEY');
?>
<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-0">
        <?php if (!empty($googleMapsKey)): ?>
            <!-- Google Maps API -->
            <div id="contact-map" style="width: 100%; height: 400px;"></div>
            <script>
                function initMap() {
                    var location = { lat: 10.7766, lng: 106.6669 }; // Địa chỉ Quận 10
                    var map = new google.maps.Map(document.getElementById('contact-map'), {
                        zoom: 15,
                        center: location,
                        scrollwheel: false
                    });
                    var marker = new google.maps.Marker({
                        position: location,
                        map: map,
                        title: '<?= htmlspecialchars(SITE_NAME) ?>'
                    });
                }
            </script>
            <script src="https://maps.googleapis.com/maps/api/js?key=<?= $googleMapsKey ?>&callback=initMap" async defer></script>
        <?php else: ?>
            <!-- Fallback: Google Maps iframe embed -->
            <div style="width: 100%; height: 400px; border: 0; overflow: hidden;">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4602324263124!2d106.66479707583808!3d10.776019559196924!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1m3!2d1396229!2d<?= urlencode($data['company']['contact_address']) ?>!5e0!3m2!1svi!2s!4v1700000000000" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        <?php endif; ?>
        
        <div class="p-3 bg-light d-flex justify-content-between align-items-center">
            <span class="small text-secondary"><i class="fa-solid fa-diamond-turn-right text-primary me-1"></i> Đường đi và định vị trên bản đồ.</span>
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($data['company']['contact_address']) ?>" target="_blank" class="btn btn-primary btn-sm px-3 fw-bold" style="border-radius: 6px;">
                <i class="fa-solid fa-map-location-dot me-1"></i> Chỉ đường
            </a>
        </div>
    </div>
</div>
