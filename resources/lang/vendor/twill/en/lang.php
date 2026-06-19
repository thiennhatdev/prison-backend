<?php
    /*
    |--------------------------------------------------------------------------
    | 5 Steps to Contribute a New Twill Localization at Ease
    |--------------------------------------------------------------------------
    | 1. Find the "lang.csv" under "lang" directory.
    | 2. Import the csv file into a blank Google Sheet.
    | 3. Each column is a language, enter the translation for a column. (tips: feel free to freeze rows and columns).
    | 4. Download the Google Sheet as CSV, replace the original "lang/lang.csv" with the new one.
    | 5. Run the command "php artisan twill:lang" to sync all lang files.
    */


return [
    'auth' => [
        'back-to-login' => 'Quay lại đăng nhập',
        'choose-password' => 'Chọn mật khẩu',
        'email' => 'Email',
        'forgot-password' => 'Quên mật khẩu',
        'login' => 'Đăng nhập',
        'login-title' => 'Đăng nhập',
        'oauth-link-title' => 'Nhập lại mật khẩu để liên kết tài khoản :provider',
        'otp' => 'Mã xác thực một lần',
        'password' => 'Mật khẩu',
        'password-confirmation' => 'Xác nhận mật khẩu',
        'reset-password' => 'Đặt lại mật khẩu',
        'reset-send' => 'Gửi liên kết đặt lại mật khẩu',
        'verify-login' => 'Xác thực đăng nhập',
        'auth-causer' => 'Xác thực',
    ],

'buckets' => [
    'intro' => 'Bạn muốn làm nổi bật nội dung nào hôm nay?',
    'none-available' => 'Không có mục nào khả dụng.',
    'none-featured' => 'Chưa có mục nổi bật nào.',
    'publish' => 'Xuất bản',
    'source-title' => 'Danh sách khả dụng',
],

'dashboard' => [
    'all-activity' => 'Tất cả hoạt động',
    'create-new' => 'Tạo mới',
    'empty-message' => 'Bạn chưa có hoạt động nào.',
    'my-activity' => 'Hoạt động của tôi',
    'my-drafts' => 'Bản nháp của tôi',
    'search-placeholder' => 'Tìm kiếm mọi thứ...',
    'statitics' => 'Thống kê',

    'search' => [
        'loading' => 'Đang tải...',
        'no-result' => 'Không tìm thấy kết quả.',
        'last-edit' => 'Chỉnh sửa lần cuối',
    ],

    'activities' => [
        'created' => 'Đã tạo',
        'updated' => 'Đã cập nhật',
        'unpublished' => 'Đã hủy xuất bản',
        'published' => 'Đã xuất bản',
        'featured' => 'Đã đánh dấu nổi bật',
        'unfeatured' => 'Đã bỏ nổi bật',
        'restored' => 'Đã khôi phục',
        'deleted' => 'Đã xóa',
        'login' => 'Đăng nhập',
        'logout' => 'Đăng xuất',
    ],

    'activity-row' => [
        'edit' => 'Chỉnh sửa',
        'view-permalink' => 'Xem liên kết',
        'by' => 'bởi',
    ],

    'unknown-author' => 'Không xác định',
],
    'dialog' => [
    'cancel' => 'Hủy',
    'ok' => 'Đồng ý',
    'title' => 'Chuyển vào thùng rác',
],

'editor' => [
    'cancel' => 'Hủy',
    'delete' => 'Xóa',
    'done' => 'Hoàn tất',
    'title' => 'Trình soạn thảo nội dung',
],

'emails' => [
    'all-rights-reserved' => 'Đã đăng ký bản quyền.',
    'hello' => 'Xin chào!',
    'problems' => 'Nếu bạn gặp sự cố khi nhấn nút ":actionText", hãy sao chép và dán liên kết bên dưới vào trình duyệt của bạn: [:url](:url)',
    'regards' => 'Trân trọng,',
],
    'fields' => [
    'block-editor' => [
        'add-content' => 'Thêm nội dung',
        'collapse-all' => 'Thu gọn tất cả',
        'create-another' => 'Tạo thêm',
        'delete' => 'Xóa',
        'expand-all' => 'Mở rộng tất cả',
        'loading' => 'Đang tải',
        'open-in-editor' => 'Mở trong trình soạn thảo',
        'preview' => 'Xem trước',
        'add-item' => 'Thêm mục',
        'clone-block' => 'Nhân bản khối',
        'select-existing' => 'Chọn có sẵn',
    ],

    'browser' => [
        'add-label' => 'Thêm',
        'attach' => 'Đính kèm',
    ],

    'files' => [
        'add-label' => 'Thêm',
    ],

    'generic' => [
        'switch-language' => 'Chuyển ngôn ngữ',
    ],

    'map' => [
        'hide' => 'Ẩn bản đồ',
        'show' => 'Hiện bản đồ',
    ],

    'medias' => [
        'btn-label' => 'Đính kèm hình ảnh',
        'crop' => 'Cắt ảnh',
        'crop-edit' => 'Chỉnh sửa vùng cắt',
        'crop-list' => 'Vùng cắt',
        'crop-save' => 'Cập nhật',
        'delete' => 'Xóa',
        'download' => 'Tải xuống',
        'edit-close' => 'Đóng thông tin',
        'edit-info' => 'Chỉnh sửa thông tin',
        'original-dimensions' => 'Kích thước gốc',
        'alt-text' => 'Văn bản thay thế',
        'caption' => 'Chú thích',
        'video-url' => 'URL video (không bắt buộc)',
    ],
],

'filter' => [
    'apply-btn' => 'Áp dụng',
    'clear-btn' => 'Xóa bộ lọc',
    'search-placeholder' => 'Tìm kiếm',
    'toggle-label' => 'Bộ lọc',
],

'footer' => [
    'version' => 'Phiên bản',
],
    'form' => [
    'content' => 'Nội dung',

    'dialogs' => [
        'delete' => [
            'confirm' => 'Xóa',
            'confirmation' => 'Bạn có chắc chắn không?<br />Hành động này không thể hoàn tác.',
            'delete-content' => 'Xóa nội dung',
            'title' => 'Xóa nội dung',
        ],
    ],

    'editor' => 'Trình soạn thảo',
    'options' => 'Tùy chọn',
],

'lang-manager' => [
    'published' => 'Đang hoạt động',
],

'lang-switcher' => [
    'edit-in' => 'Chỉnh sửa bằng',
],
    'listing' => [
    'add-new-button' => 'Thêm mới',
    'bulk-actions' => 'Thao tác hàng loạt',
    'bulk-clear' => 'Bỏ chọn',

    'columns' => [
        'featured' => 'Nổi bật',
        'name' => 'Tên',
        'published' => 'Xuất bản',
        'show' => 'Hiển thị',
        'thumbnail' => 'Ảnh đại diện',
    ],

    'dialogs' => [
        'delete' => [
            'confirm' => 'Xóa',
            'disclaimer' => 'Mục này sẽ không bị xóa vĩnh viễn mà được chuyển vào thùng rác.',
            'move-to-trash' => 'Chuyển vào thùng rác',
            'title' => 'Xóa mục',
        ],

        'destroy' => [
            'confirm' => 'Xóa vĩnh viễn',
            'destroy-permanently' => 'Xóa vĩnh viễn',
            'disclaimer' => 'Mục này sẽ không thể khôi phục được nữa.',
            'title' => 'Xóa vĩnh viễn mục',
        ],
    ],

    'dropdown' => [
        'delete' => 'Xóa',
        'destroy' => 'Xóa vĩnh viễn',
        'duplicate' => 'Nhân bản',
        'edit' => 'Chỉnh sửa',
        'publish' => 'Xuất bản',
        'feature' => 'Đánh dấu nổi bật',
        'restore' => 'Khôi phục',
        'unfeature' => 'Bỏ nổi bật',
        'unpublish' => 'Hủy xuất bản',
    ],

    'filter' => [
        'no' => 'Không',
        'yes' => 'Có',
        'all-items' => 'Tất cả',
        'draft' => 'Bản nháp',
        'mine' => 'Của tôi',
        'published' => 'Đã xuất bản',
        'trash' => 'Thùng rác',
        'not-set' => 'Chưa thiết lập',
    ],

    'filters' => [
        'all-label' => 'Tất cả :label',
    ],

    'languages' => 'Ngôn ngữ',

    'listing-empty-message' => 'Chưa có dữ liệu nào.',

    'paginate' => [
        'rows-per-page' => 'Số dòng mỗi trang:',
    ],

    'bulk-selected-item' => 'mục được chọn',
    'bulk-selected-items' => 'mục được chọn',

    'reorder' => [
        'success' => 'Đã thay đổi thứ tự :modelTitle!',
        'error' => 'Không thể thay đổi thứ tự :modelTitle. Đã xảy ra lỗi!',
    ],

    'restore' => [
        'success' => 'Đã khôi phục :modelTitle!',
        'error' => 'Không thể khôi phục :modelTitle. Đã xảy ra lỗi!',
    ],

    'bulk-restore' => [
        'success' => 'Đã khôi phục các mục :modelTitle!',
        'error' => 'Không thể khôi phục các mục :modelTitle. Đã xảy ra lỗi!',
    ],

    'force-delete' => [
        'success' => 'Đã xóa vĩnh viễn :modelTitle!',
        'error' => 'Không thể xóa vĩnh viễn :modelTitle. Đã xảy ra lỗi!',
    ],

    'bulk-force-delete' => [
        'success' => 'Đã xóa vĩnh viễn các mục :modelTitle!',
        'error' => 'Không thể xóa vĩnh viễn các mục :modelTitle. Đã xảy ra lỗi!',
    ],

    'delete' => [
        'success' => 'Đã chuyển :modelTitle vào thùng rác!',
        'error' => 'Không thể chuyển :modelTitle vào thùng rác. Đã xảy ra lỗi!',
    ],

    'bulk-delete' => [
        'success' => 'Đã chuyển các mục :modelTitle vào thùng rác!',
        'error' => 'Không thể chuyển các mục :modelTitle vào thùng rác. Đã xảy ra lỗi!',
    ],

    'duplicate' => [
        'success' => 'Đã nhân bản :modelTitle thành công!',
        'error' => 'Không thể nhân bản :modelTitle. Đã xảy ra lỗi!',
    ],

    'publish' => [
        'unpublished' => 'Đã hủy xuất bản :modelTitle!',
        'published' => 'Đã xuất bản :modelTitle!',
        'error' => 'Không thể xuất bản :modelTitle. Đã xảy ra lỗi!',
    ],

    'featured' => [
        'unfeatured' => 'Đã bỏ nổi bật :modelTitle!',
        'featured' => 'Đã đánh dấu nổi bật :modelTitle!',
        'error' => 'Không thể đánh dấu nổi bật :modelTitle. Đã xảy ra lỗi!',
    ],

    'bulk-featured' => [
        'unfeatured' => 'Đã bỏ nổi bật các mục :modelTitle!',
        'featured' => 'Đã đánh dấu nổi bật các mục :modelTitle!',
        'error' => 'Không thể đánh dấu nổi bật các mục :modelTitle. Đã xảy ra lỗi!',
    ],

    'bulk-publish' => [
        'unpublished' => 'Đã hủy xuất bản các mục :modelTitle!',
        'published' => 'Đã xuất bản các mục :modelTitle!',
        'error' => 'Không thể xuất bản các mục :modelTitle. Đã xảy ra lỗi!',
    ],
],

'main' => [
    'create' => 'Tạo mới',
    'draft' => 'Bản nháp',
    'published' => 'Đang hoạt động',
    'title' => 'Tiêu đề',
    'update' => 'Cập nhật',
],

'media-library' => [
    'files' => 'Tệp tin',
    'filter-select-label' => 'Lọc theo thẻ',
    'images' => 'Hình ảnh',
    'insert' => 'Chèn',

    'sidebar' => [
        'alt-text' => 'Văn bản thay thế',
        'caption' => 'Chú thích',
        'clear' => 'Xóa',
        'dimensions' => 'Kích thước',
        'empty-text' => 'Chưa chọn tệp nào',
        'files-selected' => 'tệp đã chọn',
        'tags' => 'Thẻ',
    ],

    'title' => 'Thư viện Media',
    'update' => 'Cập nhật',

    'unused-filter-label' => 'Chỉ hiển thị tệp chưa sử dụng',
    'no-tags-found' => 'Không tìm thấy thẻ nào.',

    'dialogs' => [
        'delete' => [
            'delete-media-title' => 'Xóa media',
            'delete-media-desc' => 'Bạn có chắc chắn không?<br />Hành động này không thể hoàn tác.',
            'delete-media-confirm' => 'Xóa',
            'title' => 'Xác nhận xóa',
            'allow-delete-multiple-medias' => 'Một số tệp đang được sử dụng và không thể xóa. Bạn có muốn xóa các tệp còn lại không?',
            'allow-delete-one-media' => 'Tệp này đang được sử dụng và không thể xóa. Bạn có muốn xóa các tệp còn lại không?',
            'dont-allow-delete-multiple-medias' => 'Các tệp này đang được sử dụng và không thể xóa.',
            'dont-allow-delete-one-media' => 'Tệp này đang được sử dụng và không thể xóa.',
        ],

        'replace' => [
            'replace-media-title' => 'Thay thế media',
            'replace-media-desc' => 'Bạn có chắc chắn không?<br />Hành động này không thể hoàn tác.',
            'replace-media-confirm' => 'Thay thế',
        ],
    ],

    'types' => [
        'single' => [
            'image' => 'hình ảnh',
            'video' => 'video',
            'file' => 'tệp',
        ],

        'multiple' => [
            'image' => 'hình ảnh',
            'video' => 'video',
            'file' => 'tệp',
        ],
    ],
],
    'modal' => [
    'create' => [
        'button' => 'Tạo mới',
        'create-another' => 'Tạo và thêm mới',
        'title' => 'Thêm mới',
    ],

    'permalink-field' => 'Liên kết tĩnh',
    'title-field' => 'Tiêu đề',

    'update' => [
        'button' => 'Cập nhật',
        'title' => 'Cập nhật',
    ],

    'done' => [
        'button' => 'Hoàn tất',
    ],
],

'nav' => [
    'admin' => 'Quản trị',
    'cms-users' => 'Người dùng CMS',
    'logout' => 'Đăng xuất',
    'media-library' => 'Thư viện Media',
    'settings' => 'Cài đặt',
    'close-menu' => 'Đóng menu',
    'profile' => 'Hồ sơ',
    'open-live-site' => 'Mở website',
],

'notifications' => [
    'reset' => [
        'action' => 'Đặt lại mật khẩu',
        'content' => 'Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.',
        'subject' => ':appName | Đặt lại mật khẩu',
    ],

    'welcome' => [
        'action' => 'Tạo mật khẩu của bạn',
        'content' => 'Bạn nhận được email này vì một tài khoản đã được tạo cho bạn trên :name.',
        'title' => 'Chào mừng',
        'subject' => ':appName | Chào mừng',
    ],
],

'overlay' => [
    'close' => 'Đóng',
],
    'previewer' => [
    'compare-view' => 'Chế độ so sánh',
    'current-revision' => 'Phiên bản hiện tại',
    'editor' => 'Trình soạn thảo',
    'last-edit' => 'Chỉnh sửa lần cuối',
    'past-revision' => 'Phiên bản trước',
    'restore' => 'Khôi phục',
    'revision-history' => 'Lịch sử phiên bản',
    'single-view' => 'Chế độ xem đơn',
    'title' => 'Xem trước thay đổi',
    'unsaved' => 'Đang xem trước với các thay đổi chưa lưu',
    'drag-and-drop' => 'Kéo và thả nội dung từ menu bên trái',
],

'publisher' => [
    'cancel' => 'Hủy',
    'current' => 'Hiện tại',
    'end-date' => 'Ngày kết thúc',
    'immediate' => 'Ngay lập tức',
    'languages' => 'Ngôn ngữ',
    'languages-published' => 'Đang hoạt động',
    'last-edit' => 'Chỉnh sửa lần cuối',
    'preview' => 'Xem trước thay đổi',

    'publish' => 'Xuất bản',
    'publish-close' => 'Xuất bản và đóng',
    'publish-new' => 'Xuất bản và tạo mới',

    'published-on' => 'Xuất bản lúc',

    'restore-draft' => 'Khôi phục thành bản nháp',
    'restore-draft-close' => 'Khôi phục thành bản nháp và đóng',
    'restore-draft-new' => 'Khôi phục thành bản nháp và tạo mới',

    'restore-live' => 'Khôi phục thành bản đã xuất bản',
    'restore-live-close' => 'Khôi phục thành bản đã xuất bản và đóng',
    'restore-live-new' => 'Khôi phục thành bản đã xuất bản và tạo mới',

    'restore-message' => 'Bạn đang chỉnh sửa một phiên bản cũ của nội dung này (được lưu bởi :user vào :date). Hãy thực hiện thay đổi nếu cần và nhấn Khôi phục để tạo một phiên bản mới.',

    'restore-success' => 'Đã khôi phục phiên bản.',

    'revisions' => 'Các phiên bản',

    'save' => 'Lưu bản nháp',
    'save-close' => 'Lưu bản nháp và đóng',
    'save-new' => 'Lưu bản nháp và tạo mới',

    'save-success' => 'Đã lưu nội dung thành công!',

    'start-date' => 'Ngày bắt đầu',

    'switcher-title' => 'Trạng thái',

    'update' => 'Cập nhật',
    'update-close' => 'Cập nhật và đóng',
    'update-new' => 'Cập nhật và tạo mới',

    'parent-page' => 'Trang cha',
    'review-status' => 'Trạng thái duyệt',
    'visibility' => 'Hiển thị',

    'scheduled' => 'Đã lên lịch',
    'expired' => 'Đã hết hạn',

    'unsaved-changes' => 'Có thay đổi chưa được lưu',

    'draft-revision' => 'Lưu thành phiên bản nháp',
    'draft-revision-close' => 'Lưu thành phiên bản nháp và đóng',
    'draft-revision-new' => 'Lưu thành phiên bản nháp và tạo mới',

    'draft-revisions-available' => 'Bạn đang xem phiên bản đã xuất bản của nội dung này. Có các phiên bản nháp mới hơn đang khả dụng.',

    'editing-draft-revision' => 'Bạn đang chỉnh sửa một phiên bản nháp của nội dung này. Hãy thực hiện thay đổi nếu cần và nhấn Lưu phiên bản hoặc Xuất bản.',
],

'select' => [
    'empty-text' => 'Không tìm thấy tùy chọn phù hợp.',
],

'uploader' => [
    'dropzone-text' => 'hoặc kéo thả tệp mới vào đây',
    'upload-btn-label' => 'Thêm mới',
],
    'user-management' => [
    '2fa' => 'Xác thực hai lớp (2FA)',

    '2fa-description' => 'Vui lòng quét mã QR này bằng ứng dụng tương thích với Google Authenticator và nhập mã xác thực một lần bên dưới trước khi lưu. Xem danh sách các ứng dụng tương thích <a href=":link" target="_blank" rel="noopener">tại đây</a>.',

    '2fa-disable' => 'Nhập mã xác thực một lần để tắt xác thực hai lớp',

    'active' => 'Đang hoạt động',
    'cancel' => 'Hủy',

    'content-fieldset-label' => 'Tài khoản',

    'description' => 'Mô tả',

    'disabled' => 'Đã vô hiệu hóa',

    'edit-modal-title' => 'Chỉnh sửa tên người dùng',

    'email' => 'Email',

    'enable-user' => 'Kích hoạt người dùng',
    'enable-user-and-close' => 'Kích hoạt người dùng và đóng',
    'enable-user-and-create-new' => 'Kích hoạt người dùng và tạo mới',

    'enabled' => 'Đã kích hoạt',

    'language' => 'Ngôn ngữ',
    'language-placeholder' => 'Chọn ngôn ngữ',

    'name' => 'Tên',

    'otp' => 'Mã xác thực một lần',

    'profile-image' => 'Ảnh đại diện',

    'role' => 'Vai trò',
    'role-placeholder' => 'Chọn vai trò',

    'title' => 'Tiêu đề',

    'trash' => 'Thùng rác',

    'update' => 'Cập nhật',
    'update-and-close' => 'Cập nhật và đóng',
    'update-and-create-new' => 'Cập nhật và tạo mới',

    'update-disabled-and-close' => 'Cập nhật người dùng bị vô hiệu hóa và đóng',
    'update-disabled-user' => 'Cập nhật người dùng bị vô hiệu hóa',
    'update-disabled-user-and-create-new' => 'Cập nhật người dùng bị vô hiệu hóa và tạo mới',

    'user-image' => 'Hình ảnh',

    'users' => 'Người dùng',

    'force-2fa-disable' => 'Tắt xác thực hai lớp',

    'force-2fa-disable-description' => 'Nhập nội dung hiển thị trong ô bên dưới để tắt xác thực hai lớp cho người dùng này',

    'force-2fa-disable-challenge' => 'Tắt xác thực hai lớp cho :user',

    'pending' => 'Đang chờ',

    'activation-pending' => 'Chờ kích hoạt',
],

'settings' => [
    'update' => 'Cập nhật',
    'cancel' => 'Hủy',
    'fieldset-label' => 'Chỉnh sửa cài đặt',
],

'permissions' => [
    'groups' => [
        'title' => 'Nhóm',
        'published' => 'Đã kích hoạt',
        'draft' => 'Đã vô hiệu hóa',
    ],

    'roles' => [
        'title' => 'Vai trò',
        'published' => 'Đã kích hoạt',
        'draft' => 'Đã vô hiệu hóa',
    ],
],
];
