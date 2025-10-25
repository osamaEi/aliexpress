<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل بائع جديد - الخطوة 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            max-width: 800px;
            width: 100%;
        }

        .registration-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header-custom {
            background: var(--gradient);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .card-header-custom h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            background: #f8f9fa;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 3px;
            background: #e0e0e0;
            z-index: -1;
        }

        .step:last-child::before {
            display: none;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: all 0.3s;
        }

        .step.active .step-circle {
            background: var(--gradient);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .step.completed .step-circle {
            background: var(--success-color);
            color: white;
        }

        .step-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .card-body-custom {
            padding: 40px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-next {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
        }

        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-back {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 14px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
            margin-top: 15px;
        }

        .btn-back:hover {
            background: var(--primary-color);
            color: white;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .icon-box i {
            font-size: 40px;
            color: var(--primary-color);
        }

        .selection-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .selection-info h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .selection-info p {
            margin-bottom: 5px;
            color: #666;
        }

        .selection-info strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-card">
            <!-- Header -->
            <div class="card-header-custom">
                <div class="icon-box">
                    <i class="ri-briefcase-line"></i>
                </div>
                <h2>حدد نشاطك التجاري</h2>
                <p>اختر المجال الذي تعمل به</p>
            </div>

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step completed">
                    <div class="step-circle"><i class="ri-check-line"></i></div>
                    <div class="step-label">المعلومات الأساسية</div>
                </div>
                <div class="step active">
                    <div class="step-circle">2</div>
                    <div class="step-label">النشاط التجاري</div>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <div class="step-label">التحقق</div>
                </div>
            </div>

            <!-- Form -->
            <div class="card-body-custom">
                @if ($errors->any())
                    <div class="alert alert-danger alert-custom mb-4">
                        <i class="ri-error-warning-line me-2"></i>
                        <strong>يرجى تصحيح الأخطاء التالية:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('seller.register.step2.process') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        <!-- Main Activity -->
                        <div class="col-12">
                            <label for="main_activity" class="form-label">
                                <i class="ri-folder-line me-1"></i> النشاط الرئيسي
                            </label>
                            <select class="form-select @error('main_activity') is-invalid @enderror"
                                    id="main_activity" name="main_activity" required onchange="updateSubActivities()">
                                <option value="">اختر النشاط الرئيسي</option>
                                @foreach($activities as $mainActivity => $subActivities)
                                    <option value="{{ $mainActivity }}" {{ old('main_activity') == $mainActivity ? 'selected' : '' }}>
                                        {{ $mainActivity }}
                                    </option>
                                @endforeach
                            </select>
                            @error('main_activity')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sub Activity -->
                        <div class="col-12">
                            <label for="sub_activity" class="form-label">
                                <i class="ri-file-list-line me-1"></i> النشاط الفرعي
                            </label>
                            <select class="form-select @error('sub_activity') is-invalid @enderror"
                                    id="sub_activity" name="sub_activity" required>
                                <option value="">اختر النشاط الفرعي</option>
                            </select>
                            @error('sub_activity')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-next">
                        التالي <i class="ri-arrow-left-line ms-2"></i>
                    </button>

                    <a href="{{ route('seller.register.step1') }}" class="btn btn-back">
                        <i class="ri-arrow-right-line me-2"></i> السابق
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script>
        const activities = @json($activities);

        function updateSubActivities() {
            const mainActivity = document.getElementById('main_activity').value;
            const subActivitySelect = document.getElementById('sub_activity');

            // Clear existing options
            subActivitySelect.innerHTML = '<option value="">اختر النشاط الفرعي</option>';

            if (mainActivity && activities[mainActivity]) {
                activities[mainActivity].forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub;
                    option.textContent = sub;
                    subActivitySelect.appendChild(option);
                });
            }
        }

        // Initialize sub-activities if main activity is already selected
        document.addEventListener('DOMContentLoaded', function() {
            const oldMain = "{{ old('main_activity') }}";
            const oldSub = "{{ old('sub_activity') }}";

            if (oldMain) {
                updateSubActivities();
                if (oldSub) {
                    document.getElementById('sub_activity').value = oldSub;
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
