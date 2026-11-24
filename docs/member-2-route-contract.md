# Thành viên 2 - route contract giao Thành viên 1

Đề nghị Thành viên 1 tích hợp các route dưới đây vào `routes/web.php`. Các view dùng URI và route name ghi trong bảng; Thành viên 2 chỉ bàn giao contract và không chỉnh file routes tích hợp.

| Method | URI | Route name | Controller action / view |
|---|---|---|---|
| GET | `/cart` | `cart.index` | `CartController@index` → `storefront.cart` |
| POST | `/cart/add` | `cart.add` | `CartController@store` |
| PATCH | `/cart/products/{product}` | `cart.product.update` | `CartController@updateProduct` (sets visible total for drawer) |
| PATCH | `/cart/items/{cart}` | `cart.update` | `CartController@update` |
| DELETE | `/cart/items/{cart}` | `cart.destroy` | `CartController@destroy` |
| POST | `/cart/clear` | `cart.clear` | `CartController@clear` |
| GET | `/checkout` | `checkout.index` | `CheckoutController@index` → `storefront.checkout`; middleware `auth`, guest chuyển tới đăng nhập với intended URL |
| POST | `/checkout/process` | `checkout.process` | `CheckoutController@store`; middleware `auth` |
| GET | `/login` | `login.show` | `AuthController@showLogin` → `storefront.auth.login` |
| POST | `/login` | `login.store` | `AuthController@login`; giữ tên POST `login` hiện có nếu cần tương thích các form cũ |
| GET | `/register` | `register.show` | `AuthController@showRegister` → `storefront.auth.register` |
| POST | `/register` | `register.store` | `AuthController@register`; giữ tên POST `register` hiện có nếu cần tương thích các form cũ |
| GET | `/forgot-password` | `password.request` | `PasswordResetController@showLinkRequestForm` → `storefront.auth.forgot-password` |
| POST | `/forgot-password` | `password.email` | `PasswordResetController@sendResetLinkEmail` |
| GET | `/reset-password/{token}` | `password.reset` | `PasswordResetController@showResetForm` |
| POST | `/reset-password` | `password.update` | `PasswordResetController@reset` |
| GET | `/account/profile` | `account.profile` | `AccountController@profile` → `storefront.account`, tab `profile`; middleware `auth` |
| PATCH | `/account/profile` | `account.profile.update` | `AccountController@updateProfile`; middleware `auth` |
| PUT | `/account/password` | `account.password.update` | `AccountController@updatePassword`; middleware `auth` |
| GET | `/account/addresses` | `account.addresses` | `AccountController@addresses` → `storefront.account`, tab `addresses`; middleware `auth` |
| POST | `/account/addresses` | `account.addresses.store` | `AccountController@storeAddress`; middleware `auth` |
| DELETE | `/account/addresses/{address}` | `account.addresses.destroy` | `AccountController@destroyAddress`; middleware `auth` |
| GET | `/orders` | `orders.index` | `AccountController@orders` → `storefront.account`, tab `orders`; middleware `auth` |

Các route `cart.*` cần hỗ trợ giỏ khách; controller xác minh quyền sở hữu dòng giỏ. Form dùng `@csrf` và method spoofing PATCH/DELETE. Tổng tiền trên view chỉ để tham khảo; backend cần tự tính lại.

## Route hiện có cần tích hợp

`routes/web.php` đã khai báo `cart.index`, `cart.add`, `checkout.index`, `checkout.process`, `login` (POST), `register` (POST), `account.profile`, `account.addresses`, `orders.index`. Các route giỏ, checkout và auth hiện là closure demo. Thành viên 1 có thể thay closure bằng controller, thêm các route thiếu trong bảng và giữ route name POST hiện tại để không làm hỏng form cũ. Tránh khai báo trùng route name.
