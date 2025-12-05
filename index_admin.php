<?php
require_once 'config.php';

// Load departments from DB
$fachbereiche_liste = [];

try {
    $sql_fachbereiche = "SELECT abteilung_id, name FROM abteilungen";
    $statement = $db_verbindung->prepare($sql_fachbereiche);
    $statement->execute();
    $fachbereiche_liste = $statement->fetchAll();
} catch (PDOException $e) {
    $fehlermeldung = "ERROR loading departments: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en"> 
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Alpha Hospital – Compassionate & Advanced Healthcare</title>
    <link rel="icon" type="image/png" href="images/favicon.png" />
    <link rel="stylesheet" href="styles.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />

    <!-- PHP dropdown CSS (no design changes) -->
    <style>
      .nav-dropdown {
        position: relative;
        display: inline-block;
      }
      .nav-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        min-width: 220px;
        padding: 10px 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border-radius: 8px;
        z-index: 9999;
      }
      .nav-dropdown-menu a {
        display: block;
        padding: 10px 16px;
        color: var(--color-text-dark);
        text-decoration: none;
        font-size: 0.95rem;
      }
      .nav-dropdown-menu a:hover {
        background: var(--bg-light);
      }
      @media (min-width: 900px) {
        .nav-dropdown:hover .nav-dropdown-menu {
          display: block;
        }
      }
      .nav-dropdown.open .nav-dropdown-menu {
        display: block;
      }
      .nav-arrow {
        font-size: 0.8rem;
        margin-left: 4px;
      }
    </style>

    <!-- Date script (unchanged) -->
    <script>
      const today = new Date();
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);

      const oneYearAhead = new Date(tomorrow);
      oneYearAhead.setFullYear(oneYearAhead.getFullYear() + 1);

      function formatDate(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const dd = String(date.getDate()).padStart(2, "0");
        return `${yyyy}-${mm}-${dd}`;
      }

      window.minBookingDate = formatDate(tomorrow);
      window.maxBookingDate = formatDate(oneYearAhead);

      document.addEventListener("DOMContentLoaded", function () {
        const dateInput = document.getElementById("date-modal");
        if (dateInput) {
          dateInput.setAttribute("min", window.minBookingDate);
          dateInput.setAttribute("max", window.maxBookingDate);
        }
      });
    </script>
  </head>

  <body>
    <header class="header">
      <div class="container header-content">
        <a href="index.php" class="logo">
          <div class="logo-icon" role="img" aria-label="Alpha Hospital Logo"></div>
          Alpha Hospital
        </a>

        <nav class="nav-menu" aria-label="Primary navigation">
          <a href="index.php" class="nav-link active">Home</a>
          <a href="about.html" class="nav-link">About Us</a>
          <a href="#services" class="nav-link">Services</a>

          <!-- B1: Original Departments link wrapped WITHOUT changes -->
          <div class="nav-dropdown">
            <a href="#" class="nav-link nav-dropdown-toggle">
              Departments <span class="nav-arrow">▾</span>
            </a>

            <!-- Dynamic PHP Menu -->
            <div class="nav-dropdown-menu">
              <?php if (!empty($fachbereiche_liste)): ?>
                <?php foreach ($fachbereiche_liste as $fachbereich): 
                      $name = htmlspecialchars($fachbereich['name']);
                      $id = (int)$fachbereich['abteilung_id'];
                ?>
                  <a href="abteilung_details.php?id=<?= $id ?>"><?= $name ?></a>
                <?php endforeach; ?>
              <?php else: ?>
                <a style="color:red;">Error loading departments</a>
              <?php endif; ?>
            </div>
          </div>

          <a href="contact.html" class="nav-link">Contact</a>
        </nav>

        <a href="admin_dashboard.php" class="btn btn-primary btn-book-appointment cta-btn-desktop">
          login
        </a>

        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M4 6h16M4 12h16M4 18h16"
              stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>

      <div id="mobileNav" class="mobile-nav" aria-hidden="true">
        <div class="menu-links">
          <a href="index.php">Home</a>
          <a href="about.php">About Us</a>
          <a href="#services">Services</a>

          <!-- Mobile PHP department list (kept simple) -->
          <strong style="margin-top:10px; display:block;">Departments</strong>
          <?php foreach ($fachbereiche_liste as $f): ?>
            <a href="abteilung_details.php?id=<?= (int)$f['abteilung_id'] ?>"
               style="margin-left:10px;"><?= htmlspecialchars($f['name']) ?></a>
          <?php endforeach; ?>

          <a href="contact.php">Contact</a>
        </div>
      </div>
    </header>

    <!-- START of the large HTML content from index.php -->
    <section class="full-hero">
      <div class="hero-content container">
        <h1 class="hero-main-title">
          Compassionate Care.<br />Advanced Medicine.
        </h1>
        <p class="hero-main-subtitle">
          Alpha Hospital has been delivering exceptional healthcare with
          expertise and compassion for over 25 years.
        </p>
      </div>
    </section>
    <main class="container main-content">
      <section class="main-card">
        <div class="hero-grid">
          <div class="hero-left">
            <h2 class="hero-title">
              Committed to <strong>Excellence</strong> in Patient Care
            </h2>
            <p class="hero-lead">
              Our dedicated team of doctors, nurses, and healthcare
              professionals provide personalized treatment plans with the latest
              medical technology to ensure the best outcomes for every patient.
            </p>

            <div class="hero-cta">
              <a class="btn btn-primary btn-book-appointment" href="#">
                Book Appointment Now
              </a>
              <a class="btn btn-outline-primary" href="contact.php">
                Contact Us
              </a>
            </div>

            <div class="feature-strip">
              <div class="feature">
                <div class="feature-icon" aria-hidden="true">✅</div>
                <div class="feature-text">JCI Accredited</div>
              </div>
              <div class="feature">
                <div class="feature-icon" aria-hidden="true">⭐</div>
                <div class="feature-text">Award-Winning Service</div>
              </div>
              <div class="feature">
                <div class="feature-icon" aria-hidden="true">🤝</div>
                <div class="feature-text">Trusted Care</div>
              </div>
            </div>
          </div>

          <div class="hero-right">
            <div class="hero-image-frame">
              <img src="images/img12.png" alt="Images of doctors and nurses" />
            </div>
          </div>
        </div>
      </section>

      <!-- Excellence in numbers -->
      <section
        class="secondary"
        style="background: var(--color-secondary); padding-top: 5rem; padding-bottom: 5rem;"
      >
        <h2 class="section-title">Excellence in Numbers</h2>
        <div
          class="cards-grid"
          style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;"
        >
          <div class="info-card" style="text-align:center; border:2px solid var(--color-primary);">
            <p
              style="font-size:3.5rem; font-weight:800; color:var(--color-primary); margin:0;"
            >98.5%</p>
            <p
              style="margin-top:0.5rem; font-weight:600;"
            >Patient Satisfaction</p>
          </div>

          <div class="info-card" style="text-align:center; border:2px solid var(--color-primary);">
            <p
              style="font-size:3.5rem; font-weight:800; color:var(--color-primary); margin:0;"
            >5-Star</p>
            <p
              style="margin-top:0.5rem; font-weight:600;"
            >Safety Rating</p>
          </div>

          <div class="info-card" style="text-align:center; border:2px solid var(--color-primary);">
            <p
              style="font-size:3.5rem; font-weight:800; color:var(--color-primary); margin:0;"
            >20+</p>
            <p
              style="margin-top:0.5rem; font-weight:600;"
            >Years of Service</p>
          </div>
        </div>
      </section>

      <!-- Core Values -->
      <section class="secondary">
        <h2 class="section-title">The Alpha Difference: Our Core Values</h2>
        <p
          class="hero-lead"
          style="text-align:center; max-width:800px; margin:0 auto 2.5rem;"
        >
          We build trust through clinical excellence, unwavering compassion, and
          ethical practice. Discover the values that guide every decision we
          make.
        </p>

        <div class="cards-grid" style="grid-template-columns:repeat(auto-fit,minmax(250px,1fr));">
          <article class="info-card" style="text-align:center; padding-top:2rem;">
            <div class="feature-icon" style="font-size:3rem; color:var(--color-primary);">❤️</div>
            <h3>Compassion First</h3>
            <p>
              We believe in treating every patient with empathy, respect, and
              dignity, ensuring emotional comfort is prioritized alongside
              medical treatment.
            </p>
          </article>

          <article class="info-card" style="text-align:center; padding-top:2rem;">
            <div class="feature-icon" style="font-size:3rem; color:var(--color-primary);">💡</div>
            <h3>Clinical Innovation</h3>
            <p>
              We are committed to continuous learning and the rapid adoption of
              proven, cutting-edge medical technologies to improve patient
              outcomes.
            </p>
          </article>

          <article class="info-card" style="text-align:center; padding-top:2rem;">
            <div class="feature-icon" style="font-size:3rem; color:var(--color-primary);">🛡️</div>
            <h3>Safety and Integrity</h3>
            <p>
              Patient safety is non-negotiable. We maintain the highest
              standards of professional and ethical integrity in all clinical
              and administrative operations.
            </p>
          </article>
        </div>
      </section>

      <!-- Services -->
      <section class="secondary" id="services">
        <h2 class="section-title">Our Specialty Services</h2>

        <div class="cards-grid">
          <article class="info-card">
            <img src="images/img03.jpg" alt="Emergency department in action" />
            <h3>Rapid Emergency Care</h3>
            <p>
              Our emergency department is staffed with skilled professionals,
              ready to provide immediate and effective care 24/7.
            </p>
          </article>

          <article class="info-card">
            <img src="images/img04.jpg" alt="Modern surgical suites" />
            <h3>Advanced Surgical Suites</h3>
            <p>
              Equipped with cutting-edge technology, our operating rooms support
              complex procedures with precision and safety.
            </p>
          </article>

          <article class="info-card">
            <img src="images/img05.jpg" alt="Diagnostic imaging lab" />
            <h3>Precision Diagnostics</h3>
            <p>
              We offer comprehensive imaging and lab services, including MRI,
              CT, and X-ray, for fast and accurate diagnosis.
            </p>
          </article>

          <article class="info-card">
            <img src="images/img09.jpg" alt="Cancer treatment" />
            <h3>Advanced Medical Expertise</h3>
            <p>
              Access our world-class Specialized Medical Care, featuring
              dedicated units in Cardiology, Oncology, Neurology, and more.
            </p>
          </article>

          <article class="info-card">
            <img src="images/img10.jpg" alt="Recovery and wellness" />
            <h3>Recovery and Wellness</h3>
            <p>
              Our Rehabilitation and Therapy Services help patients regain
              strength, mobility, and independence.
            </p>
          </article>

          <article class="info-card">
            <img src="images/img11.jpg" alt="Mind and body health" />
            <h3>Mind and Body Health</h3>
            <p>
              Mental and Behavioral Health Services for depression, anxiety,
              and substance use disorders.
            </p>
          </article>
        </div>
      </section>
      <!-- Featured Physicians -->
      <section class="secondary">
        <h2 class="section-title">Meet Our Featured Physicians</h2>
        <p
          class="hero-lead"
          style="text-align: center; max-width: 800px; margin: 0 auto 2.5rem"
        >
          Our award-winning physicians lead the way in innovative medicine,
          combining years of experience with compassionate care.
        </p>

        <div
          class="cards-grid"
          style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))"
        >
          <article
            class="info-card"
            style="padding: 0; overflow: hidden; text-align: center"
          >
            <img
              src="images/doctor-card-1.jpg"
              alt="Dr. Elena Rodriguez"
              style="
                height: 300px;
                object-fit: cover;
                object-position: top center;
                margin-bottom: 0;
              "
            />
            <div style="padding: 1.5rem">
              <h3 style="margin-bottom: 0.25rem">Dr. Elena Rodriguez</h3>
              <p
                style="
                  color: var(--color-primary);
                  font-weight: 600;
                  margin-bottom: 0.5rem;
                  font-size: 0.95rem;
                "
              >
                Chief of Cardiology
              </p>
              <a
                class="btn btn-outline-primary"
                style="padding: 0.5rem 1rem; font-size: 0.9rem"
                href="#"
                >View Profile</a
              >
            </div>
          </article>

          <article
            class="info-card"
            style="padding: 0; overflow: hidden; text-align: center"
          >
            <img
              src="images/doctor-card-2.jpg"
              alt="Dr. Marcus Chen"
              style="
                height: 300px;
                object-fit: cover;
                object-position: top center;
                margin-bottom: 0;
              "
            />
            <div style="padding: 1.5rem">
              <h3 style="margin-bottom: 0.25rem">Dr. Marcus Chen</h3>
              <p
                style="
                  color: var(--color-primary);
                  font-weight: 600;
                  margin-bottom: 0.5rem;
                  font-size: 0.95rem;
                "
              >
                Director of Orthopedics
              </p>
              <a
                class="btn btn-outline-primary"
                style="padding: 0.5rem 1rem; font-size: 0.9rem"
                href="#"
                >View Profile</a
              >
            </div>
          </article>

          <article
            class="info-card"
            style="padding: 0; overflow: hidden; text-align: center"
          >
            <img
              src="images/doctor-card-3.jpg"
              alt="Dr. Amelia Khan"
              style="
                height: 300px;
                object-fit: cover;
                object-position: top center;
                margin-bottom: 0;
              "
            />
            <div style="padding: 1.5rem">
              <h3 style="margin-bottom: 0.25rem">Dr. Amelia Khan</h3>
              <p
                style="
                  color: var(--color-primary);
                  font-weight: 600;
                  margin-bottom: 0.5rem;
                  font-size: 0.95rem;
                "
              >
                Pediatric Specialist
              </p>
              <a
                class="btn btn-outline-primary"
                style="padding: 0.5rem 1rem; font-size: 0.9rem"
                href="#"
                >View Profile</a
              >
            </div>
          </article>
        </div>
      </section>

      <!-- Patient Testimonials -->
      <section class="secondary">
        <h2 class="section-title">What Our Patients Say</h2>
        <div
          class="cards-grid"
          style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))"
        >
          <article
            class="info-card"
            style="border-left: 5px solid var(--color-primary)"
          >
            <p
              style="
                font-style: italic;
                color: var(--color-text-dark);
                line-height: 1.7;
              "
            >
              "The care team was attentive, knowledgeable, and truly
              compassionate. They made a frightening experience manageable. The
              nurses felt like family. Highly recommend Alpha Hospital to anyone
              seeking expert, personalized care."
            </p>
            <p
              style="
                font-weight: 700;
                color: var(--color-primary);
                margin-top: 1rem;
              "
            >
              — Sarah K., Surgery Patient
            </p>
          </article>

          <article
            class="info-card"
            style="border-left: 5px solid var(--color-primary)"
          >
            <p
              style="
                font-style: italic;
                color: var(--color-text-dark);
                line-height: 1.7;
              "
            >
              "From diagnostics to discharge, the communication was seamless. I
              felt fully informed about my treatment plan and the results were
              better than I could have hoped for. A truly world-class medical
              team."
            </p>
            <p
              style="
                font-weight: 700;
                color: var(--color-primary);
                margin-top: 1rem;
              "
            >
              — David L., Cardiology Patient
            </p>
          </article>
        </div>
      </section>

      <!-- Community Impact -->
      <section class="secondary">
        <h2 class="section-title">Our Community Impact</h2>
        <div
          class="hero-grid"
          style="
            grid-template-columns: 1fr 1.2fr;
            gap: 3rem;
            align-items: center;
          "
        >
          <div class="hero-image-frame" style="height: 400px">
            <img
              src="images/img13.jpg"
              alt="Hospital staff leading a free health clinic in the community"
              style="object-fit: cover"
            />
          </div>

          <div class="hero-left" style="padding: 0">
            <h3
              class="hero-title"
              style="font-size: 1.8rem; margin-bottom: 1rem"
            >
              Investing in a Healthier Tomorrow
            </h3>
            <p class="hero-lead" style="margin-top: 0">
              We are dedicated to going beyond the hospital walls. Our outreach
              programs provide free health screenings, educational workshops,
              and wellness resources to underserved populations in our region.
            </p>
            <ul>
              <li style="margin-bottom: 0.5rem; font-weight: 500">
                Free Annual Flu Shot Clinics
              </li>
              <li style="margin-bottom: 0.5rem; font-weight: 500">
                Nutrition and Diabetes Management Workshops
              </li>
              <li style="margin-bottom: 0.5rem; font-weight: 500">
                Local School Health Education Programs
              </li>
            </ul>
            <a class="btn btn-secondary" href="#" style="margin-top: 1rem"
              >Learn About Our Foundation</a
            >
          </div>
        </div>
      </section>

      <!-- News -->
      <section
        class="secondary"
        style="
          background: var(--bg-light);
          padding-top: 5rem;
          padding-bottom: 5rem;
        "
      >
        <h2 class="section-title">Health Resources & Latest News</h2>
        <p
          class="hero-lead"
          style="text-align: center; max-width: 800px; margin: 0 auto 2.5rem"
        >
          Stay informed with the latest updates from Alpha Hospital, including
          health tips, research breakthroughs, and community events.
        </p>

        <div
          class="cards-grid"
          style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr))"
        >
          <article class="info-card">
            <img
              src="images/news-1.jpg"
              alt="Image of a running shoe"
              style="height: 200px; object-fit: cover;"
            />
            <p
              style="
                color: var(--color-text-medium);
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
              "
            >
              MAY 10, 2025 | WELLNESS
            </p>
            <h3>5 Tips for Starting a Joint-Friendly Running Routine</hh3>
            <a
              class="btn btn-secondary"
              style="padding: 0.5rem 1rem; font-size: 0.9rem"
              href="#"
              >Read Article</a
            >
          </article>

          <article class="info-card">
            <img
              src="images/news-2.jpg"
              alt="Image of two scientists working"
              style="height: 200px; object-fit: cover;"
            />
            <p
              style="
                color: var(--color-text-medium);
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
              "
            >
              APRIL 25, 2025 | RESEARCH
            </p>
            <h3>Breakthrough in Non-Invasive Cancer Screening Technology</h3>
            <a
              class="btn btn-secondary"
              style="padding: 0.5rem 1rem; font-size: 0.9rem"
              href="#"
              >Read Article</a
            >
          </article>

          <article class="info-card">
            <img
              src="images/news-3.jpg"
              alt="Image of a blood pressure monitor"
              style="height: 200px; object-fit: cover;"
            />
            <p
              style="
                color: var(--color-text-medium);
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
              "
            >
              APRIL 1, 2025 | CARDIOLOGY
            </p>
            <h3>Understanding and Managing Hypertension at Home</h3>
            <a
              class="btn btn-secondary"
              style="padding: 0.5rem 1rem; font-size: 0.9rem"
              href="#"
              >Read Article</a
            >
          </article>
        </div>
      </section>

      <!-- Departments section -->
      <section class="secondary" id="departments">
        <h2 class="section-title">Ready for Advanced, Compassionate Care?</h2>
        <div
          class="hero-grid"
          style="
            grid-template-columns: 1fr;
            max-width: 900px;
            margin: 0 auto;
            background: var(--bg-white);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
          "
        >
          <div style="text-align: center">
            <p class="hero-lead">
              Whether you're scheduling a routine visit or facing an emergency,
              we are here for you 24/7.
            </p>
            <div
              class="hero-cta"
              style="justify-content: center; margin-bottom: 2rem"
            >
              <a class="btn btn-primary btn-book-appointment" href="#"
                >Book Online</a
              >
              <a class="btn btn-secondary" href="contact.php"
                >See All Locations</a
              >
            </div>

            <p
              style="
                font-size: 1.1rem;
                font-weight: 600;
                color: var(--color-text-dark);
              "
            >
              Main Campus: 123 Health Ave, Wellness City
            </p>

            <div
              class="department-image-container"
              style="
                padding-bottom: 30%;
                margin-top: 1.5rem;
                background-color: var(--color-secondary);
              "
            >
              <img
                src="images/map-placeholder.jpg"
                class="department-image"
                alt="Map showing Alpha Hospital location (Image coming soon)"
              />
            </div>
            <p
              style="
                font-size: 0.9rem;
                color: var(--color-text-medium);
                margin-top: 0.5rem;
              "
            >
              (Image Placeholder: Replace with an embedded map or relevant
              location image.)
            </p>
          </div>
        </div>
      </section>
    </main>

    <!-- FOOTER -->
    <footer class="footer" aria-label="Footer">
      <div class="container footer-grid">
        <div>
          <h3 class="footer-logo">Alpha Hospital</h3>
          <p>
            Dedicated to providing safe, compassionate, and professional
            healthcare for our community.
          </p>
        </div>
        <div>
          <h4>Quick Links</h4>
          <ul>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Patient Portal</a></li>
            <li><a href="#">Billing</a></li>
          </ul>
        </div>
        <div>
          <h4>Departments</h4>
          <ul>
            <li><a href="#">Emergency</a></li>
            <li><a href="#">Pediatrics</a></li>
            <li><a href="#">Surgery</a></li>
          </ul>
        </div>
        <div>
          <h4>Contact</h4>
          <p>123 Health Ave, Wellness City</p>
          <p>Phone: (555) 123-4567</p>
        </div>
      </div>
    </footer>


      </div>
    </div>

    <div id="toastNotification" class="toast">
      Appointment requested successfully!
    </div>

    <script src="script.js"></script>
    <script>
document.getElementById("department-modal").addEventListener("change", function () {
    let depId = this.value;

    fetch("load_doctors.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "department_id=" + depId
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("doctor-modal").innerHTML = data;
    });
});

// --- Submit Form with Validation + DB Saving ---
document.getElementById("appointmentForm").addEventListener("submit", function(e) {
    e.preventDefault();

    // Keep your script.js validation system
    const requiredFields = document.querySelectorAll("[data-required='true']");
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.classList.add("input-error");
            return; 
        } else {
            field.classList.remove("input-error");
        }
    }

    let formData = new FormData(this);

    fetch("save_appointment.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(response => {
        document.getElementById("toastNotification").textContent =
            "Appointment booked successfully!";
        document.getElementById("toastNotification").classList.add("show");

        setTimeout(() => {
            location.reload();
        }, 1500);
    });
});
</script>


  </body>
</html>
