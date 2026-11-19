<?php 
return [
    'commerce' => ['title' => 'Đơn hàng và lưu ký', 'route' => 'admin.commerce.index', 'can' => 'access-commerce'],
    'accessLogs' => [
        'title' => 'Nhật ký truy cập',
        'route' => 'admin.access-logs.index',
        'can' => 'access-admin-operations',
    ],
    'thongke' => [
        'title' => 'Thống kê',
        'route' => 'admin.thongke.index',
        'can' => 'access-admin-operations',
    ],
    'prisoner' => [
        'title' => 'Phạm nhân',
        'module' => true,
        'can' => 'access-admin-operations',
    ],
    'visitationSchedule' => [
        'title' => 'Lịch thăm gặp',
        'module' => true,
        'can' => 'access-admin-operations',
    ],
    'customers' => [
        'title' => 'User',
        'route' => 'admin.customers.index',
        'can' => 'access-admin-operations',
    ],
    'post' => [
        'title' => 'Tin tức',
        'module' => true,
        'can' => 'access-admin-operations',
    ],
    'prisonRule' => [
        'title' => 'Nội quy',
        'module' => true,
        'can' => 'access-admin-operations',
    ],
    'survey' => [
        'title' => 'Đánh giá',
        'module' => true,
        'can' => 'access-admin-operations',
    ],
];
