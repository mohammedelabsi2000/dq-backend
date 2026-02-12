<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة</title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .error-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-icon {
            font-size: 120px;
            color: #764ba2;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .error-code {
            font-size: 80px;
            font-weight: 800;
            color: #333;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 2px 2px 0 rgba(118, 75, 162, 0.1);
        }
        
        .error-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: #6c757d;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(118, 75, 162, 0.4);
            color: white;
        }
        
        .btn-home i {
            margin-left: 10px;
        }
        
        .search-box {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }
        
        .search-box input {
            border-radius: 50px;
            border: 2px solid #e9ecef;
            padding: 12px 25px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }
        
        .search-box button {
            border-radius: 50px;
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            border: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            background: #5a6268;
        }
        
        .help-links {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .help-link {
            color: #764ba2;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .help-link:hover {
            color: #5a3d7a;
            text-decoration: underline;
        }
        
        .help-link i {
            margin-left: 5px;
        }
        
        @media (max-width: 768px) {
            .error-card {
                padding: 30px 20px;
            }
            
            .error-icon {
                font-size: 80px;
            }
            
            .error-code {
                font-size: 60px;
            }
            
            .error-title {
                font-size: 24px;
            }
            
            .error-message {
                font-size: 16px;
            }
            
            .btn-home {
                padding: 12px 30px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-mosque"></i>
        </div>
        
        <div class="error-code">404</div>
        <h1 class="error-title">الصفحة غير موجودة</h1>
        
        <div class="error-message">
            <p>عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها.</p>
            <p class="mb-0">تحقق من عنوان URL أو عد إلى الصفحة الرئيسية.</p>
        </div>
        
        <div class="d-grid gap-3">
            <a href="{{ url('/') }}" class="btn-home">
                <i class="fas fa-home"></i>
                العودة إلى الرئيسية
            </a>
            
            <div class="mt-3">
                <button onclick="history.back()" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                    <i class="fas fa-arrow-right me-1"></i>
                    الرجوع للصفحة السابقة
                </button>
            </div>
        </div>
        
        <!-- <div class="search-box">
            <h5 class="mb-3">البحث في الموقع</h5>
            <form action="{{ route('users.index') ?? '#' }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="ابحث عن مستخدم، مسجد، خطة..." autocomplete="off">
                <button type="submit" class="btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div> -->
        
        <!-- <div class="help-links">
            <a href="{{ route('dashboard') ?? '#' }}" class="help-link">
                <i class="fas fa-tachometer-alt"></i>
                لوحة التحكم
            </a>
            <a href="{{ route('users.index') ?? '#' }}" class="help-link">
                <i class="fas fa-users"></i>
                المستخدمين
            </a>
            <a href="{{ route('mosques.index') ?? '#' }}" class="help-link">
                <i class="fas fa-mosque"></i>
                المساجد
            </a>
            <a href="{{ route('plans.index') ?? '#' }}" class="help-link">
                <i class="fas fa-layer-group"></i>
                الخطط
            </a>
        </div> -->
        
        <div class="mt-4 text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            إذا كنت تعتقد أن هذه مشكلة، يرجى الاتصال بالدعم الفني
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto redirect to home after 30 seconds
        setTimeout(function() {
            window.location.href = '{{ url("/") }}';
        }, 30000);
    </script>
</body>
</html>