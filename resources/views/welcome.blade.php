<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DailyWorks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #eef4ff 0%, #f8fafc 100%);
            font-family: 'Tahoma', 'Segoe UI', sans-serif;
        }
        .hero-card {
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card hero-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <span class="badge bg-primary-subtle text-primary-emphasis mb-3">DailyWorks</span>
                    <h1 class="display-5 fw-bold">نظام إدارة عقود وأعمال صيانة الطرق</h1>
                    <p class="text-muted mt-3">واجهة عربية متكاملة لإدارة العقود، الأعمال اليومية، التراكمات، الصور، والفواتير والتقارير.</p>
                </div>

                <div class="row g-3 text-center mb-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <h4 class="fw-bold">العقود</h4>
                                <p class="mb-0 text-muted">إدارة العقود والمهندسين ومندوبي الإشراف.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <h4 class="fw-bold">الأعمال</h4>
                                <p class="mb-0 text-muted">تسجيل بنود العمل اليومية وتحديد الكميات.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <h4 class="fw-bold">التقارير</h4>
                                <p class="mb-0 text-muted">طباعة تقارير دقيقة حسب العقد أو التاريخ.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">إنشاء حساب</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
