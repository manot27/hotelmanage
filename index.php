<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sales Details Dashboard</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Optional: Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --primary-grad-left: #0f62fe;
      --primary-grad-right: #00b894;
      --card-bg: #ffffff;
      --muted: #6c757d;
    }
    body {
      font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(135deg, var(--primary-grad-left) 0%, #0ea5a4 40%, #00b894 100%);
      min-height: 100vh;
      color: #222;
      padding-bottom: 80px;
    }

    /* Top header */
    .topbar {
      background: linear-gradient(90deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
      backdrop-filter: blur(6px);
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .brand-title {
      color: #fff;
      font-weight: 600;
      letter-spacing: 0.2px;
    }
    .nav-icon {
      color: rgba(255,255,255,0.9);
      font-size: 1.05rem;
    }

    /* Main container */
    .app-container {
      max-width: 1200px;
      margin: 28px auto;
      padding: 24px;
      background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(2,6,23,0.35);
      border: 1px solid rgba(255,255,255,0.06);
    }

    /* Cards */
    .metric-card {
      background: var(--card-bg);
      border-radius: 10px;
      padding: 18px;
      box-shadow: 0 6px 18px rgba(2,6,23,0.08);
      border: 1px solid rgba(15,20,30,0.04);
      min-height: 170px;
    }
    .metric-title {
      font-size: 0.95rem;
      color: var(--muted);
      font-weight: 600;
    }
    .metric-value {
      font-size: 2.4rem;
      font-weight: 700;
      color: #0f62fe;
      margin-top: 6px;
    }
    .metric-sub {
      color: #8a8f98;
      font-size: 0.85rem;
      margin-bottom: 12px;
    }
    .field-label {
      font-size: 0.82rem;
      color: #6b7280;
      margin-bottom: 6px;
      display:block;
      font-weight:600;
    }
    .field-input {
      width:100%;
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid #e6e9ef;
      background: #fbfdff;
      font-size: 0.9rem;
    }

    /* Footer activity bar */
    .activity-bar {
      margin-top: 18px;
      padding: 12px 16px;
      border-radius: 8px;
      background: linear-gradient(90deg, rgba(15,98,254,0.12), rgba(0,184,148,0.08));
      display:flex;
      justify-content:space-between;
      align-items:center;
      color:#fff;
      font-weight:600;
    }

    /* Responsive tweaks */
    @media (max-width: 767.98px) {
      .metric-value { font-size: 1.8rem; }
      .app-container { padding: 16px; margin: 16px; }
    }
  </style>
</head>
<body>

  <!-- Top navigation -->
  <nav class="navbar navbar-expand-lg topbar">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
          <i class="bi bi-gear-fill" style="color:#0f62fe;"></i>
        </div>
        <div class="brand-title ms-2">Sales Details</div>
      </a>

      <!-- Mobile toggler (three lines) -->
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon" style="display:inline-block;width:28px;height:18px;position:relative;">
          <span style="position:absolute;left:0;right:0;height:2px;background:#fff;top:0;border-radius:2px;"></span>
          <span style="position:absolute;left:0;right:0;height:2px;background:#fff;top:8px;border-radius:2px;"></span>
          <span style="position:absolute;left:0;right:0;height:2px;background:#fff;top:16px;border-radius:2px;"></span>
        </span>
      </button>

      <div class="collapse navbar-collapse" id="topNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item me-3 d-flex align-items-center">
            <i class="bi bi-speedometer2 nav-icon me-2"></i>
            <span class="text-white">Dashboard</span>
          </li>
          <li class="nav-item me-3 d-flex align-items-center">
            <i class="bi bi-bar-chart-line nav-icon me-2"></i>
            <span class="text-white">Sales Tracker</span>
          </li>
          <li class="nav-item me-3 d-flex align-items-center">
            <i class="bi bi-wallet2 nav-icon me-2"></i>
            <span class="text-white">Expenses Tracker</span>
          </li>
          <li class="nav-item me-3 d-flex align-items-center">
            <i class="bi bi-shop nav-icon me-2"></i>
            <span class="text-white">Store Performance</span>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main app container -->
  <main class="app-container">
    <!-- Top row: small nav icons (desktop) -->
    <div class="d-none d-md-flex mb-3 align-items-center gap-3">
      <div class="badge bg-white text-dark p-2 rounded-3"><i class="bi bi-grid-fill me-2"></i> Dashboard</div>
      <div class="badge bg-white text-dark p-2 rounded-3"><i class="bi bi-graph-up me-2"></i> Sales</div>
      <div class="badge bg-white text-dark p-2 rounded-3"><i class="bi bi-basket me-2"></i> Purchases</div>
      <div class="badge bg-white text-dark p-2 rounded-3"><i class="bi bi-gear me-2"></i> Resources</div>
      <div class="badge bg-white text-dark p-2 rounded-3"><i class="bi bi-clipboard-data me-2"></i> Analysis</div>
    </div>

    <!-- Four metric cards -->
    <div class="row g-3">
      <!-- Card 1 -->
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="metric-card">
          <div class="metric-title">Sales</div>
          <div class="metric-value">67<span style="font-size:1rem;color:#8a8f98">%</span></div>
          <div class="metric-sub">Average Year Over Year</div>

          <label class="field-label">Profit</label>
          <input class="field-input" placeholder="—" />

          <label class="field-label mt-2">Purchases / Store</label>
          <input class="field-input" placeholder="Store name" />
        </div>
      </div>

      <!-- Card 2 -->
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="metric-card">
          <div class="metric-title">Sales</div>
          <div class="metric-value">5<span style="font-size:1rem;color:#8a8f98">%</span></div>
          <div class="metric-sub">Gross Sales</div>

          <label class="field-label">Profit</label>
          <input class="field-input" placeholder="—" />

          <label class="field-label mt-2">Purchases / Store</label>
          <input class="field-input" placeholder="Store name" />
        </div>
      </div>

      <!-- Card 3 -->
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="metric-card">
          <div class="metric-title">Expenses</div>
          <div class="metric-value">55.5<span style="font-size:1rem;color:#8a8f98">%</span></div>
          <div class="metric-sub">Average Year Over Year</div>

          <label class="field-label">Profit</label>
          <input class="field-input" placeholder="—" id="expenses-profit" />

          <label class="field-label mt-2">Revenues to Store</label>
          <input class="field-input" placeholder="Store name" />
        </div>
      </div>

      <!-- Card 4 -->
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="metric-card">
          <div class="metric-title">Umango Store</div>
          <div class="metric-value">2<span style="font-size:1rem;color:#8a8f98">%</span></div>
          <div class="metric-sub">Average Year Over Year</div>

          <label class="field-label">Profit</label>
          <input class="field-input" placeholder="—" />

          <label class="field-label mt-2">Purchases / Store</label>
          <input class="field-input" placeholder="Store name" />
        </div>
      </div>
    </div>

    <!-- Activity / footer bar -->
    <div class="activity-bar mt-4">
      <div class="d-flex gap-3 align-items-center">
        <i class="bi bi-exclamation-circle-fill" style="opacity:0.95"></i>
        <div>Missing Stores</div>
      </div>
      <div class="d-flex gap-3 align-items-center">
        <div class="small">Restaurant</div>
        <div class="small">Store</div>
        <div class="small">Activity Bar</div>
      </div>
    </div>
  </main>

  <!-- Bootstrap 5 JS (Popper included) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Small enhancement: focus visual on Expenses Profit when page loads (simulates cursor pointing)
    document.addEventListener('DOMContentLoaded', function () {
      const el = document.getElementById('expenses-profit');
      if (el) {
        // briefly highlight to simulate cursor focus
        el.style.boxShadow = '0 0 0 3px rgba(15,98,254,0.12)';
        setTimeout(() => el.style.boxShadow = '', 1200);
      }
    });
  </script>
</body>
</html>
