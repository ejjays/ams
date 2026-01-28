<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School Management System</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../IMAGE/sms.png" />

    <!-- App CSS -->
    <link rel="stylesheet" href="../app/css/sms.css">

    <!-- Vendor CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm stylish-navbar navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="./sms.php">
                <img src="../IMAGE/sms.png" alt="SMS logo" width="40" height="40" class="me-2" loading="eager">
                School Management
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <ul class="navbar-nav mx-auto gap-4">
                    <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#about-us">About Us</a></li>
                    <li class="nav-item"><a class="nav-link nav-underline fw-bold" href="#contact">Contact Us</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <!-- CHANGED: go straight to login to avoid auth banner -->
                        <a class="btn btn-primary px-4 py-2 rounded-pill fw-bold" href="./login.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main id="main">
        <!-- Hero Section -->
        <section class="hero-slant-section position-relative">
            <div class="container position-relative z-2">
                <div class="row align-items-center">
                    <div class="col-md-6 hero-text">
                        <h1>WELCOME TO <br><span class="text-primary">ACCREDITATION MANAGEMENT SYSTEM</span></h1>
                        <p>Efficiently manage student records, faculty activities, and school operations — all in one place.</p>
                        <!-- CHANGED: go straight to login to avoid auth banner -->
                        <a href="./login.php" class="btn-get-started">Get Started</a>
                    </div>
                </div>
            </div>
            <div class="slanted-bg"></div>
            <div class="hero-logo-slant">
                <img src="../IMAGE/sms.png" alt="School Management logo" width="300" height="300" loading="lazy" />
            </div>
            <div class="hero-student-slant">
                <img src="../IMAGE/hero.png" alt="" width="500" loading="lazy" />
            </div>
        </section>

        <!-- Core Features Section -->
        <section id="features" class="py-5 text-white" style="background: linear-gradient(#ffffff44, #011f4b53);">
            <div class="container">
                <div class="text-end mb-4">
                    <h2 class="fw-bold text-primary">Core Features</h2>
                    <p class="text-light">Explore what our School Management System offers.</p>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-3">
                    <!-- Feature Box 1 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-user-graduate fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Enrollment</h6>
                            <p class="text-light small mb-0">Student registration made simple.</p>
                        </div>
                    </div>

                    <!-- Feature Box 2 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-book-open fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Subjects</h6>
                            <p class="text-light small mb-0">Manage courses and subjects easily.</p>
                        </div>
                    </div>

                    <!-- Feature Box 3 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-users fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Students</h6>
                            <p class="text-light small mb-0">View and update student profiles.</p>
                        </div>
                    </div>

                    <!-- Feature Box 4 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-chalkboard-teacher fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Faculty</h6>
                            <p class="text-light small mb-0">Manage faculty members and classes.</p>
                        </div>
                    </div>

                    <!-- Feature Box 5 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-calendar-alt fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Schedule</h6>
                            <p class="text-light small mb-0">Class scheduling made efficient.</p>
                        </div>
                    </div>

                    <!-- Feature Box 6 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-clipboard-list fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Grades</h6>
                            <p class="text-light small mb-0">Record and track academic performance.</p>
                        </div>
                    </div>

                    <!-- Feature Box 7 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-user-shield fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">User Roles</h6>
                            <p class="text-light small mb-0">Manage permissions and access.</p>
                        </div>
                    </div>

                    <!-- Feature Box 8 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-bell fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Notifications</h6>
                            <p class="text-light small mb-0">Stay updated with announcements.</p>
                        </div>
                    </div>

                    <!-- Feature Box 9 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-chart-line fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Analytics</h6>
                            <p class="text-light small mb-0">Track school performance visually.</p>
                        </div>
                    </div>

                    <!-- Feature Box 10 -->
                    <div class="feature-box">
                        <div class="text-center px-2 py-3 rounded-3 h-100" style="background-color: rgba(255,255,255,0.05);">
                            <i class="fas fa-cogs fa-lg text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Settings</h6>
                            <p class="text-light small mb-0">Configure your system preferences.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section id="about-us" class="py-5 text-white" style="background: linear-gradient(to right, #ffffff56, #3399ff23);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-4 mb-md-0 text-center">
                        <img src="../IMAGE/studs.jpg" class="img-fluid rounded w-75" alt="Students on campus" width="900" height="600" loading="lazy">
                    </div>
                    <div class="col-md-6">
                        <h2 class="fw-bold">About Us</h2>
                        <p>
                            Our School Management System is a modern solution designed to make academic and administrative
                            processes simple, efficient, and unified. Built for both educators and learners, we aim to provide
                            a digital space where everything just works — from enrollment to research.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Us Section -->
        <section id="contact" class="py-3 text-white" style="background: linear-gradient(135deg, #0a0a3a1d, #1465b163);">
            <div class="container text-center">
                <h2 class="fw-bold mb-4">Contact Us</h2>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="fw-bold"><i class="fas fa-map-marker-alt me-2"></i> Address</h5>
                            <p>Bestlink College of the Philippines, Quirino Highway, Quezon City, Metro Manila</p>
                        </div>
                        <div class="mb-3">
                            <h5 class="fw-bold"><i class="fas fa-phone me-2"></i> Contact Number</h5>
                            <p><a href="tel:+63212345678">(02) 1234-5678</a> / <a href="tel:+639171234567">0917-123-4567</a></p>
                        </div>
                        <div class="mb-3">
                            <h5 class="fw-bold"><i class="fas fa-envelope me-2"></i> Email</h5>
                            <p><a href="mailto:sms.support@bestlink.edu.ph">sms.support@bestlink.edu.ph</a></p>
                        </div>
                        <div class="mb-3">
                            <h5 class="fw-bold"><i class="fas fa-clock me-2"></i> Office Hours</h5>
                            <p>Monday - Friday: 8:00 AM to 5:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer Section -->
    <footer class="text-center py-4" style="background-color: #002a80;">
        <div class="container" style="max-width: 600px;">
            <p class="mb-0 text-white">&copy; 2025 School Management System. All rights reserved.</p>
        </div>
    </footer>

    <!-- Vendor JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>