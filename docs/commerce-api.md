# API đặt hàng, gửi tiền lưu ký và SePay

## Cấu hình

Migration `2026_11_13_000000_create_commerce_tables.php` tạo các bảng mới, không sửa dữ liệu cũ.
Trong `.env`, điền:

```dotenv
SEPAY_BANK=MB
SEPAY_ACCOUNT_NUMBER=SO_TAI_KHOAN_NHAN
SEPAY_ACCOUNT_NAME="TEN CHU TAI KHOAN"
SEPAY_WEBHOOK_KEY=KHOA_BI_MAT_DU_DAI
PAYMENT_EXPIRES_MINUTES=30
```

Chạy `php artisan config:clear` sau khi đổi cấu hình. Không đưa khóa thật vào Git hoặc frontend.
Thiếu cấu hình, API tạo thanh toán trả 503 và không tạo đơn dở dang.

Trong SePay:

- Liên kết tài khoản nhận tiền tương ứng.
- Tạo webhook tiền vào, URL HTTPS công khai: `https://TEN_MIEN/api/webhooks/sepay`.
- Chọn xác thực **API Key**, nhập cùng khóa `SEPAY_WEBHOOK_KEY`, định dạng JSON.
- Gửi cả giao dịch không có mã để giữ lịch sử đối soát; không bật bỏ qua giao dịch thiếu mã.
- Có thể cấu hình hai tiền tố DH và LK để lấy trường `code`. Backend cũng nhận diện đúng một mã DH/LK kèm 20 ký tự trong `content` nếu `code` trống.
- Bật gửi lại khi HTTP status ngoài 2xx. Thành công trả 201 và `{"success":true}`. Khi lỗi lâu, kiểm tra nhật ký SePay và gửi lại webhook; chưa có tác vụ đồng bộ bù bằng API SePay.
- Bản này dùng tài khoản ngân hàng trực tiếp, không dùng tài khoản ảo/subAccount.

Tài liệu nguồn: https://docs.sepay.vn/tich-hop-webhooks.html

## Xác thực và quyền

API thân nhân dùng `Authorization: Bearer TOKEN`, `Accept: application/json`.
Chỉ tài khoản CUSTOMER đang hoạt động được truy cập. Khi tạo đơn/gửi tiền, số điện thoại phải có trong `phones` của đúng phạm nhân đã được xuất bản; chuẩn hóa đầu 84 thành 0.
Danh sách/chi tiết đơn, lưu ký, thanh toán chỉ trả bản ghi của tài khoản hiện tại.
Webhook không dùng token thân nhân, dùng `Authorization: Apikey KHOA_BI_MAT`.

## Endpoint

| Method | Path | Chức năng |
|---|---|---|
| GET | /api/products | Hàng đang bán, 30 mục/trang |
| POST | /api/orders | Tạo đơn, trả `data` và `payment` |
| GET | /api/orders | Đơn của tôi, 20 mục/trang |
| GET | /api/orders/{id} | Chi tiết đơn, mặt hàng và thanh toán |
| POST | /api/deposit-requests | Tạo yêu cầu gửi tiền, trả `data` và `payment` |
| GET | /api/deposit-requests | Lịch sử yêu cầu của tôi, 20 mục/trang |
| GET | /api/deposit-requests/{id} | Chi tiết yêu cầu |
| GET | /api/payments/{id} | Thông tin thanh toán và QR |
| POST | /api/webhooks/sepay | Nhận giao dịch SePay |

POST tạo đơn/yêu cầu bắt buộc header `Idempotency-Key`, tối đa 100 ký tự chữ, số, `_`, `-`. Frontend sinh UUID cho một thao tác, giữ cùng key khi gửi lại. Cùng key và dữ liệu trả cùng kết quả; khác dữ liệu trả 409. Một key không dùng chung giữa đơn hàng và lưu ký.

### Tạo đơn

```json
{
  "prisoner_id": 1,
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 2, "quantity": 1}
  ],
  "note": "Ghi chú giao hàng"
}
```

Giá được lấy từ database, lưu snapshot tên và giá. Không nhận giá/tổng tiền từ client.
Tối đa 100 loại hàng khác nhau, mỗi loại 1–100 đơn vị. Tổng đơn tối đa 100.000.000 VNĐ.
Danh mục hiện có giá và bật/tắt bán; chưa quản lý tồn kho hoặc giữ hàng.

### Tạo yêu cầu gửi tiền

```json
{"prisoner_id": 1, "amount": 500000}
```

Số tiền nguyên VNĐ từ 1.000 đến 100.000.000. Đây là giới hạn kỹ thuật ban đầu, cần điều chỉnh theo quy trình đơn vị trước khi vận hành.

### Phản hồi tạo

`data` chứa đơn/yêu cầu; `payment` có `id`, `code`, `amount`, `bank`, `account_number`, `account_name`, `expires_at`, `status`, `qr_url`, `is_expired`.
Frontend hiển thị QR và thông tin chuyển khoản, đọc `/api/payments/{id}` định kỳ (ví dụ 5 giây) khi đang chờ; dừng khi đã PAID, REVIEW, hết hạn hoặc rời trang.
Chỉ trạng thái từ backend xác nhận thanh toán.

## Trạng thái và ghi sổ

- Thanh toán: `PENDING`, `PAID`, `REVIEW`. Hết hạn được tính từ `expires_at`, hiển thị qua `is_expired`; không có job tự đổi trạng thái.
- Đơn hàng: `AWAITING_PAYMENT` → `PAID` → `PREPARING` → `DELIVERED`.
- Lưu ký: `AWAITING_PAYMENT` → `CREDITED` sau khi nhận đúng tiền. Không cần cán bộ duyệt.
- Giao dịch: `MATCHED` hoặc `REVIEW`; `RECEIVED` chỉ tồn tại bên trong transaction xử lý.

Webhook yêu cầu mã, tài khoản, chiều tiền và số tiền khớp, yêu cầu còn hạn và PENDING.
Thanh toán, đơn/yêu cầu và bút toán ghi có cập nhật trong một DB transaction.
`sepay_id` duy nhất chống xử lý lại. Mỗi yêu cầu lưu ký và mỗi giao dịch chỉ có tối đa một bút toán.
Đơn thanh toán riêng; không trừ tiền lưu ký. Bản này là sổ nhận tiền lưu ký, chưa triển khai các nghiệp vụ chi/rút/hoàn tiền hoặc số dư khả dụng.

Khoản thiếu/thừa, mã không rõ, tài khoản sai, tiền ra, đã thanh toán hoặc quá hạn được giữ ở REVIEW, không cộng tiền tự động. Không tự gộp nhiều khoản chuyển thiếu. Nếu thanh toán PENDING nhận khoản khớp mã nhưng lệch tiền/quá hạn, chuyển thanh toán sang REVIEW; đơn/yêu cầu vẫn chờ, frontend/admin phải đọc thêm trạng thái payment.

## Admin

Menu **Đơn hàng và lưu ký** tại `/admin/commerce`, yêu cầu đăng nhập Twill.

- Quản lý mặt hàng: thêm, sửa giá/mô tả, ngừng bán.
- Xem đơn và các mặt hàng; chỉ đơn đã thanh toán mới được chuẩn bị và xác nhận giao.
- Xem yêu cầu lưu ký và sổ ghi có tự động.
- Xem giao dịch, lọc `REVIEW` để đối soát, có mã SePay và lý do.

Bản đầu chỉ cung cấp danh sách đối soát, chưa có thao tác phân bổ lại tiền, gộp giao dịch hoặc hoàn tiền từ admin. Các khoản REVIEW cần xử lý theo quy trình đơn vị; không sửa trực tiếp bút toán để tránh mất lịch sử.

## Kiểm thử trước vận hành

Chạy `php artisan test --filter=CommerceTest`. Các test dùng SQLite memory, không tác động database thật. Test chống lặp webhook, ghi có, lệch tiền, hết hạn, sai tài khoản, tiền ra, mã lạ, giá từ server, retry tạo đơn và quyền sở hữu.
Cần kiểm tra thêm bằng giao dịch ngân hàng nhỏ với SePay thật sau khi cấu hình HTTPS và tài khoản. Chưa kiểm chứng kết nối ngân hàng thực tế hoặc tải đồng thời trên MySQL bằng bộ test này.
