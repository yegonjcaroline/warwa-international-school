<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/layout.php';
if (current_user()) redirect(current_user()['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php');
start_page('Welcome');
?>
<section class="hero-panel hero-with-photo">
  <div class="hero-copy">
    <p class="eyebrow !text-[#d8bd74]">WARWA INTERNATIONAL SCHOOL</p>
    <h1 class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight text-white sm:text-4xl">A confident start to your next chapter.</h1>
    <p class="mt-4 max-w-xl text-sm leading-7 text-slate-200">Explore practical courses, build valuable skills and learn at your own pace. Your course enrollment is just KSh 5.</p>
    <div class="mt-7 flex flex-wrap gap-3"><a class="btn-primary !bg-[#137c78]" href="<?= e(app_url('register.php')) ?>">Create student account</a><a class="btn-secondary" href="<?= e(app_url('courses.php')) ?>">Browse courses</a></div>
    <div class="mt-8 flex gap-7 text-xs text-slate-200"><span><b class="kpi-accent">01</b> Simple enrollment</span><span><b class="kpi-accent">02</b> Secure payments</span></div>
  </div>
  <figure class="hero-photo-wrap">
    <img class="hero-photo" src="<?= e(app_url('assets/images/kenyan-graduates.png')) ?>" alt="Kenyan graduates celebrating in navy gowns with teal and gold accents" fetchpriority="high">
    <figcaption class="sr-only">Warwa graduates celebrating their achievement.</figcaption>
  </figure>
</section>
<section class="mt-9"><div class="mb-4 flex items-end justify-between"><div><p class="eyebrow">LEARNING OPPORTUNITIES</p><h2 class="card-title mt-1">Featured courses</h2></div><a class="text-sm font-bold text-teal-700" href="<?= e(app_url('courses.php')) ?>">View all →</a></div>
<div class="grid gap-4 md:grid-cols-3"><?php require_once __DIR__ . '/config/database.php'; foreach ($pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY id LIMIT 3") as $course): ?><article class="card course-card"><span class="course-code"><?= e($course['code']) ?></span><h3 class="mt-3 text-lg font-bold text-navy-900"><?= e($course['title']) ?></h3><p class="mt-2 flex-1 text-sm leading-6 text-slate-600"><?= e($course['description']) ?></p><div class="mt-4 flex justify-between border-t border-slate-100 pt-3 text-xs text-slate-500"><span><?= e($course['duration']) ?></span><span>Enrollment KSh 5</span></div></article><?php endforeach ?></div></section>
<?php end_page(); ?>
