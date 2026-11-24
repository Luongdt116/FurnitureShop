{{-- Storefront Main Footer --}}
<footer id="store-main-footer" class="footer">
    <div class="container">
        <div class="footer-grid">
            {{-- Column 1: Brand Info --}}
            <div>
                <div class="logo footer-logo mb-3">
                    <div class="logo-icon"><i class="fas fa-couch"></i></div>
                    <div>
                        <span class="logo-text text-white">AURA LIVING</span>
                        <span class="logo-tagline" style="color: #a89f95;">Nội thất chuẩn Bắc Âu</span>
                    </div>
                </div>
                <p style="font-size: 0.88rem; line-height: 1.7; color: #a89f95; margin-bottom: 20px;">
                    Kiến tạo phong cách sống vượt thời gian. 100% sản phẩm đạt chứng chỉ an toàn phát thải E1 châu Âu, bảo vệ sức khỏe cho mọi thế hệ gia đình.
                </p>
                <div style="font-size: 0.88rem; color: #a89f95; line-height: 1.8;">
                    <div><i class="fas fa-location-dot" style="color: var(--accent); margin-right: 8px;"></i> Showroom: 720A Điện Biên Phủ, Phường Bến Nghé, Quận 1, TP.HCM</div>
                    <div><i class="fas fa-phone" style="color: var(--accent); margin-right: 8px;"></i> Tổng đài CSKH: 1800 6868 (Miễn phí cước)</div>
                    <div><i class="fas fa-envelope" style="color: var(--accent); margin-right: 8px;"></i> Email: contact@auraliving.vn</div>
                </div>
            </div>

            {{-- Column 2: About AURA --}}
            <div>
                <h4 class="footer-col-title">Về AURA LIVING</h4>
                <ul class="footer-links ps-0">
                    <li><a href="{{ route('about') }}">Câu chuyện thương hiệu</a></li>
                    <li><a href="{{ route('about') }}">Xưởng sản xuất thủ công</a></li>
                    <li><a href="{{ route('catalog.index') }}">Dự án căn hộ mẫu 2026</a></li>
                    <li><a href="{{ route('about') }}">Kiến thức bài trí Japandi</a></li>
                    <li><a href="{{ route('contact') }}">Tuyển dụng chuyên viên thiết kế</a></li>
                </ul>
            </div>

            {{-- Column 3: Policies & Services --}}
            <div>
                <h4 class="footer-col-title">Chính Sách & Dịch Vụ</h4>
                <ul class="footer-links ps-0">
                    <li><a href="{{ route('policy.terms') }}">Chính sách bảo hành kết cấu 5 năm</a></li>
                    <li><a href="{{ route('policy.returns') }}">Chính sách đổi trả minh bạch 7 ngày</a></li>
                    <li><a href="{{ route('policy.terms') }}">Vận chuyển & Lắp đặt tận căn hộ</a></li>
                    <li><a href="{{ route('policy.terms') }}">Bảo mật thông tin & thanh toán an toàn</a></li>
                    <li><a href="{{ route('policy.returns') }}">Quy trình thẩm định chất lượng</a></li>
                </ul>
            </div>

            {{-- Column 4: Newsletter --}}
            <div>
                <h4 class="footer-col-title">Đăng Ký Nhận Bản Tin</h4>
                <p style="font-size: 0.85rem; color: #a89f95; margin-bottom: 14px;">
                    Nhận ngay voucher ưu đãi 10% cho đơn hàng đầu tiên và cẩm nang thiết kế nội thất mùa mới.
                </p>
                <form id="store-newsletter-form" action="{{ route('newsletter.subscribe') ?? '#' }}" method="POST">
                    @csrf
                    <div class="d-flex gap-2">
                        <input type="email" name="email" id="store-newsletter-email" 
                               placeholder="Email của bạn..." required 
                               class="form-control form-input" 
                               style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); color: #fff;">
                        <button type="submit" id="store-newsletter-btn" class="btn btn-accent btn-sm">
                            Đăng ký
                        </button>
                    </div>
                </form>
                <div class="mt-4">
                    <span style="font-size: 0.8rem; color: #a89f95; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 8px;">Kết nối cùng AURA</span>
                    <div class="d-flex gap-3 text-white fs-5">
                        <a href="#" class="text-white-50 text-hover-gold"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white-50 text-hover-gold"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white-50 text-hover-gold"><i class="fab fa-pinterest"></i></a>
                        <a href="#" class="text-white-50 text-hover-gold"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="footer-bottom">
            <div>&copy; {{ date('Y') }} AURA LIVING JSC. Mã số doanh nghiệp: 0318992182 do Sở KH&ĐT TP.HCM cấp.</div>
            <div>Đồ án Website Bán Đồ Nội Thất Cao Cấp &bull; Nền tảng thương mại điện tử Aura Living</div>
        </div>
    </div>
</footer>
