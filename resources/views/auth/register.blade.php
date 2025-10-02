<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Inscription - Gestion Déchets Médicaux</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("{{ asset('backend/assets/img/slides-1.jpg') }}") no-repeat center center fixed;
            background-size: cover;
            opacity: 0.1;
            z-index: -1;
        }

        .card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .step {
            display: none;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease-in-out;
        }

        .step.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
            animation: slideInUp 0.5s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .progress {
            height: 12px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-2px);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8, #6b46c1);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #218838, #1a9b81);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }

        .input-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .password-toggle:hover {
            color: #495057;
            background: rgba(0, 0, 0, 0.05);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f1aeb5);
            color: #721c24;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }

        .title-gradient {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .step-indicator {
            text-align: center;
            margin-bottom: 30px;
        }

        .step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            line-height: 40px;
            font-weight: bold;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .step-number.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: scale(1.1);
        }

        .step-number.completed {
            background: #28a745;
            color: white;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            background: #e9ecef;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak {
            background: #dc3545;
            width: 25%;
        }

        .strength-fair {
            background: #ffc107;
            width: 50%;
        }

        .strength-good {
            background: #20c997;
            width: 75%;
        }

        .strength-strong {
            background: #28a745;
            width: 100%;
        }

        .form-floating {
            position: relative;
        }

        .form-floating .form-control {
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }

        .form-floating label {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 1rem 0.75rem;
            overflow: hidden;
            text-align: start;
            text-overflow: ellipsis;
            white-space: nowrap;
            pointer-events: none;
            border: 1px solid transparent;
            transform-origin: 0 0;
            transition: opacity 0.1s ease-in-out, transform 0.1s ease-in-out;
        }

        .form-floating .form-control:focus~label,
        .form-floating .form-control:not(:placeholder-shown)~label {
            opacity: 0.65;
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }

        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .invalid-feedback.d-block {
            display: block !important;
        }

        @media (max-width: 768px) {
            .card {
                margin: 10px;
                border-radius: 15px;
            }

            .btn {
                width: 100%;
                margin: 5px 0;
            }

            .d-flex.justify-content-between .btn {
                width: 48%;
            }
        }
    </style>
</head>

<body>
    <main class="d-flex align-items-center justify-content-center min-vh-100 py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card p-5">
                        <div class="text-center mb-4">
                            <h2 class="title-gradient mb-2">Créer un compte</h2>
                            <p class="text-muted">Gestion Déchets Médicaux</p>
                        </div>

                        <!-- Step indicators -->
                        <div class="step-indicator">
                            <span class="step-number active" id="stepNum1">1</span>
                            <span class="step-number" id="stepNum2">2</span>
                            <span class="step-number" id="stepNum3">3</span>
                        </div>

                        <!-- Progress bar -->
                        <div class="progress mb-4">
                            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 33%;"
                                aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- Error Alert -->
                        <div id="errorAlert" class="alert alert-danger" style="display: none;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <span id="errorMessage"></span>
                        </div>

                        @if(session('success'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('success') }}
                        </div>
                        @endif

                        <form id="regForm" method="POST" action="{{ route('register.store') }}">
                            @csrf

                            <!-- Step 1: Infos personnelles -->
                            <div class="step active" data-step="0">
                                <h4 class="mb-4 text-center">
                                    <i class="bi bi-person-fill text-primary me-2"></i>
                                    Informations personnelles
                                </h4>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" name="firstname"
                                                class="form-control @error('firstname') is-invalid @enderror"
                                                id="firstname" placeholder="Prénom" value="{{ old('firstname') }}"
                                                required>
                                            <label for="firstname">Prénom</label>
                                        </div>
                                        @error('firstname')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" name="lastname"
                                                class="form-control @error('lastname') is-invalid @enderror"
                                                id="lastname" placeholder="Nom" value="{{ old('lastname') }}" required>
                                            <label for="lastname">Nom</label>
                                        </div>
                                        @error('lastname')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary" onclick="nextStep()">
                                        Suivant <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Step 2: Infos compte -->
                            <div class="step" data-step="1">
                                <h4 class="mb-4 text-center">
                                    <i class="bi bi-envelope-fill text-primary me-2"></i>
                                    Informations de compte
                                </h4>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="text" name="username"
                                            class="form-control @error('username') is-invalid @enderror" id="username"
                                            placeholder="Nom d'utilisateur" value="{{ old('username') }}" required>
                                        <label for="username">Nom d'utilisateur</label>
                                    </div>
                                    @error('username')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror" id="email"
                                            placeholder="Email" value="{{ old('email') }}" required>
                                        <label for="email">Adresse email</label>
                                    </div>
                                    @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                                        <i class="bi bi-arrow-left"></i> Précédent
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep()">
                                        Suivant <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Step 3: Mots de passe -->
                            <div class="step" data-step="2">
                                <h4 class="mb-4 text-center">
                                    <i class="bi bi-shield-lock-fill text-primary me-2"></i>
                                    Sécurité du compte
                                </h4>

                                <div class="mb-3">
                                    <div class="form-floating position-relative">
                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            placeholder="Mot de passe" required>
                                        <label for="password">Mot de passe</label>
                                        <button type="button" class="password-toggle"
                                            onclick="togglePassword('password')">
                                            <i class="bi bi-eye" id="password-icon"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength">
                                        <div class="password-strength-bar" id="passwordStrength"></div>
                                    </div>
                                    <small class="text-muted" id="passwordHelp">
                                        Le mot de passe doit contenir au moins 8 caractères
                                    </small>
                                    @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <div class="form-floating position-relative">
                                        <input type="password" name="password_confirmation" class="form-control"
                                            id="password_confirmation" placeholder="Confirmer le mot de passe" required>
                                        <label for="password_confirmation">Confirmer le mot de passe</label>
                                        <button type="button" class="password-toggle"
                                            onclick="togglePassword('password_confirmation')">
                                            <i class="bi bi-eye" id="password_confirmation-icon"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                                        <i class="bi bi-arrow-left"></i> Précédent
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn">
                                        <span class="spinner-border spinner-border-sm me-2 d-none"
                                            id="submitSpinner"></span>
                                        <i class="bi bi-check-circle me-2"></i>
                                        Créer le compte
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted">
                                Déjà un compte ?
                                <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Se connecter</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentStep = 0;
        const steps = document.querySelectorAll(".step");
        const progressBar = document.getElementById("progressBar");
        const stepNumbers = document.querySelectorAll(".step-number");

        function showStep(n) {
            steps.forEach((step, index) => {
                step.classList.toggle("active", index === n);
            });

            stepNumbers.forEach((stepNum, index) => {
                stepNum.classList.remove("active", "completed");
                if (index === n) {
                    stepNum.classList.add("active");
                } else if (index < n) {
                    stepNum.classList.add("completed");
                }
            });

            const progress = ((n + 1) / steps.length) * 100;
            progressBar.style.width = progress + "%";
            progressBar.setAttribute("aria-valuenow", progress);
        }

        function nextStep() {
            if (!validateStep(currentStep)) return false;
            if (currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function validateStep(n) {
            const inputs = steps[n].querySelectorAll("input[required]");
            let isValid = true;

            inputs.forEach(input => {
                clearError(input);

                if (!input.value.trim()) {
                    showInputError(input, "Ce champ est requis");
                    isValid = false;
                } else {
                    if (input.type === "email" && !isValidEmail(input.value)) {
                        showInputError(input, "Adresse email invalide");
                        isValid = false;
                    }

                    if (input.name === "username" && input.value.length < 3) {
                        showInputError(input, "Le nom d'utilisateur doit contenir au moins 3 caractères");
                        isValid = false;
                    }

                    if (input.name === "password" && input.value.length < 8) {
                        showInputError(input, "Le mot de passe doit contenir au moins 8 caractères");
                        isValid = false;
                    }

                    if (input.name === "password_confirmation") {
                        const password = document.getElementById("password").value;
                        if (input.value !== password) {
                            showInputError(input, "Les mots de passe ne correspondent pas");
                            isValid = false;
                        }
                    }
                }
            });

            return isValid;
        }

        function showInputError(input, message) {
            input.classList.add("is-invalid");
            let feedback = input.closest('.mb-3, .mb-4').querySelector('.invalid-feedback:not(.d-block)');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                input.closest('.mb-3, .mb-4').appendChild(feedback);
            }
            feedback.textContent = message;
            feedback.classList.add('d-block');
        }

        function clearError(input) {
            input.classList.remove("is-invalid");
            const feedbacks = input.closest('.mb-3, .mb-4').querySelectorAll('.invalid-feedback:not(.d-block)');
            feedbacks.forEach(feedback => {
                feedback.classList.remove('d-block');
                feedback.textContent = '';
            });
        }

        function showError(message) {
            const errorAlert = document.getElementById("errorAlert");
            const errorMessage = document.getElementById("errorMessage");
            errorMessage.innerHTML = message;
            errorAlert.style.display = "block";
            errorAlert.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + "-icon");

            if (field.type === "password") {
                field.type = "text";
                icon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                field.type = "password";
                icon.classList.replace("bi-eye-slash", "bi-eye");
            }
        }

        document.getElementById("password").addEventListener("input", function() {
            const password = this.value;
            const strengthBar = document.getElementById("passwordStrength");
            const helpText = document.getElementById("passwordHelp");

            let strength = 0;
            let message = "";

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            strengthBar.className = "password-strength-bar";

            switch (strength) {
                case 0:
                case 1:
                    strengthBar.classList.add("strength-weak");
                    message = "Mot de passe faible";
                    break;
                case 2:
                    strengthBar.classList.add("strength-fair");
                    message = "Mot de passe correct";
                    break;
                case 3:
                case 4:
                    strengthBar.classList.add("strength-good");
                    message = "Bon mot de passe";
                    break;
                case 5:
                    strengthBar.classList.add("strength-strong");
                    message = "Mot de passe très fort";
                    break;
            }

            helpText.textContent = message;
            helpText.className = strength >= 3 ? "text-success" : "text-muted";
        });

        document.getElementById("regForm").addEventListener("submit", function(e) {
            if (!validateStep(currentStep)) {
                e.preventDefault();
                return;
            }

            const submitBtn = document.getElementById("submitBtn");
            const spinner = document.getElementById("submitSpinner");

            submitBtn.disabled = true;
            spinner.classList.remove("d-none");
        });

        document.querySelectorAll("input").forEach(input => {
            input.addEventListener("blur", function() {
                if (this.value.trim()) {
                    clearError(this);
                }
            });

            input.addEventListener("input", function() {
                if (this.classList.contains("is-invalid") && this.value.trim()) {
                    clearError(this);
                }
            });
        });

        // Initialisation et gestion des erreurs serveur
        document.addEventListener('DOMContentLoaded', function() {
            const hasErrors = document.querySelectorAll('.form-control.is-invalid').length > 0;

            if (hasErrors) {
                // Déterminer quelle étape contient des erreurs
                const step0HasErrors = document.querySelector('[data-step="0"] .is-invalid') !== null;
                const step1HasErrors = document.querySelector('[data-step="1"] .is-invalid') !== null;
                const step2HasErrors = document.querySelector('[data-step="2"] .is-invalid') !== null;

                if (step2HasErrors) {
                    currentStep = 2;
                } else if (step1HasErrors) {
                    currentStep = 1;
                } else if (step0HasErrors) {
                    currentStep = 0;
                }

                showStep(currentStep);

                // Afficher message d'erreur général
                const errorMessages = [];
                document.querySelectorAll('.invalid-feedback.d-block').forEach(el => {
                    if (el.textContent.trim()) {
                        errorMessages.push(el.textContent.trim());
                    }
                });

                if (errorMessages.length > 0) {
                    showError(errorMessages.join('<br>'));
                }
            } else {
                showStep(0);
            }
        });
    </script>
</body>

</html>