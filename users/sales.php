<?php
// sale.php
// Single-file sales tracker with mysqli (DB: business)
// Update DB credentials if needed
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'business';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}
$mysqli->set_charset('utf8mb4');

function json_exit($arr) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

// AJAX endpoints
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_products') {
    $sql = "SELECT id, productname, category, quantity, purchasing_price, selling_price, type FROM products ORDER BY productname";
    $res = $mysqli->query($sql);
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $res->free();
    }
    json_exit($rows);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_product') {
        $pname = trim($_POST['productname'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $quantity = floatval($_POST['quantity'] ?? 0);
        $pp = floatval($_POST['purchasing_price'] ?? 0);
        $sp = floatval($_POST['selling_price'] ?? 0);
        $type = $_POST['type'] ?? '';

        if ($pname === '' || $category === '' || $type === '') {
            json_exit(['success' => false, 'message' => 'Missing required fields']);
        }

        $stmt = $mysqli->prepare("INSERT INTO products (productname, category, quantity, purchasing_price, selling_price, type) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) json_exit(['success' => false, 'message' => 'Prepare failed']);
        $stmt->bind_param('ssddds', $pname, $category, $quantity, $pp, $sp, $type);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) json_exit(['success' => true, 'message' => 'Product added']);
        json_exit(['success' => false, 'message' => 'Could not add product']);
    }

    if ($action === 'add_sale') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $qty = floatval($_POST['quantity_sold'] ?? 0);
        $total = floatval($_POST['total_price'] ?? 0);

        if ($product_id <= 0 || $qty <= 0) {
            json_exit(['success' => false, 'message' => 'Invalid product or quantity']);
        }

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare("SELECT quantity, type FROM products WHERE id = ? FOR UPDATE");
            if (!$stmt) throw new Exception('Prepare failed');
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $product = $res->fetch_assoc();
            $stmt->close();

            if (!$product) {
                $mysqli->rollback();
                json_exit(['success' => false, 'message' => 'Product not found']);
            }

            $currentQty = floatval($product['quantity']);
            $type = $product['type'];

            if (($type === 'Unit' || $type === 'Piece') && floor($qty) != $qty) {
                $mysqli->rollback();
                json_exit(['success' => false, 'message' => "Product type '{$type}' must be sold in whole numbers"]);
            }

            if ($qty > $currentQty) {
                $mysqli->rollback();
                json_exit(['success' => false, 'message' => 'Not enough stock']);
            }

            $ins = $mysqli->prepare("INSERT INTO sales (product_id, quantity_sold, total_price) VALUES (?, ?, ?)");
            if (!$ins) throw new Exception('Prepare failed (insert sale)');
            $ins->bind_param('idd', $product_id, $qty, $total);
            $ins->execute();
            $ins->close();

            $upd = $mysqli->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            if (!$upd) throw new Exception('Prepare failed (update product)');
            $upd->bind_param('di', $qty, $product_id);
            $upd->execute();
            $upd->close();

            $mysqli->commit();
            json_exit(['success' => true, 'message' => 'Sale recorded']);
        } catch (Exception $e) {
            $mysqli->rollback();
            json_exit(['success' => false, 'message' => 'Database error']);
        }
    }

    json_exit(['success' => false, 'message' => 'Unknown action']);
}

// If embed=1 return only inner HTML + inline init script
$embed = isset($_GET['embed']) && $_GET['embed'] == '1';
if ($embed) {
    ?>
    <!-- Embedded Sale Tracker (fragment) -->
    <div id="sale-embedded">
      <div class="container-fluid p-0">
        <div class="row g-3 mb-3">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sale Tracker</h5>
            <div>
              <button class="btn btn-sm btn-success" id="openAddProductBtn_emb"><i class="bi bi-plus-lg"></i> Add Product</button>
              <button class="btn btn-sm btn-outline-secondary" id="reloadProducts_emb"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-body p-2">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="productsTable_emb">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Purchase</th>
                    <th>Selling</th>
                    <th>Type</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-primary" id="btnAddSale_emb"><i class="bi bi-cart-plus"></i> Add Sale</button>
        </div>
      </div>

      <!-- Add Product Modal (embedded) -->
      <div class="modal fade" id="addProductModal_emb" tabindex="-1">
        <div class="modal-dialog">
          <form id="addProductForm_emb" class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add Product</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-2">
                <label class="form-label">Product Name</label>
                <input name="productname" class="form-control" required />
              </div>
              <div class="mb-2">
                <label class="form-label">Category</label>
                <input name="category" class="form-control" required />
              </div>
              <div class="row g-2">
                <div class="col-6 mb-2">
                  <label class="form-label">Quantity</label>
                  <input name="quantity" type="number" step="0.001" class="form-control" value="0" required />
                </div>
                <div class="col-6 mb-2">
                  <label class="form-label">Type</label>
                  <select name="type" class="form-select" required>
                    <option value="">Select type</option>
                    <option value="Unit">Unit</option>
                    <option value="Kilogram">Kilogram</option>
                    <option value="Litre">Litre</option>
                    <option value="Piece">Piece</option>
                    <option value="Millilitre">Millilitre</option>
                  </select>
                </div>
              </div>
              <div class="row g-2">
                <div class="col-6 mb-2">
                  <label class="form-label">Purchasing Price</label>
                  <input name="purchasing_price" type="number" step="0.01" class="form-control" required />
                </div>
                <div class="col-6 mb-2">
                  <label class="form-label">Selling Price</label>
                  <input name="selling_price" type="number" step="0.01" class="form-control" required />
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
              <button class="btn btn-primary" type="submit">Save Product</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Sale Modal (embedded) -->
      <div class="modal fade" id="addSaleModal_emb" tabindex="-1">
        <div class="modal-dialog">
          <form id="addSaleForm_emb" class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add Sale</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-2">
                <label class="form-label">Product</label>
                <select id="saleProductSelect_emb" class="form-select" required>
                  <option value="">Select product</option>
                </select>
              </div>

              <div id="saleProductDetails_emb" style="display:none;">
                <div class="mb-2">
                  <label class="form-label">Category</label>
                  <input id="saleCategory_emb" class="form-control" readonly />
                </div>
                <div class="row g-2">
                  <div class="col-6 mb-2">
                    <label class="form-label">Type</label>
                    <input id="saleType_emb" class="form-control" readonly />
                  </div>
                  <div class="col-6 mb-2">
                    <label class="form-label">Available Qty</label>
                    <input id="saleAvailable_emb" class="form-control" readonly />
                  </div>
                </div>

                <div class="mb-2">
                  <label class="form-label">Selling Price (per unit)</label>
                  <input id="salePrice_emb" class="form-control" readonly />
                </div>

                <div class="mb-2">
                  <label class="form-label">Quantity to Sell</label>
                  <input id="saleQty_emb" type="number" step="0.001" class="form-control" required />
                </div>

                <div class="mb-2">
                  <label class="form-label">Total Price</label>
                  <input id="saleTotal_emb" class="form-control" readonly />
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
              <button class="btn btn-success" type="submit">Confirm Sale</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
    // initSaleEmbedded: initializes embedded UI (called automatically when fragment is injected)
    function initSaleEmbedded() {
      const apiBase = 'sale.php';
      let products = {};

      function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]; });
      }

      function fetchProducts() {
        fetch(apiBase + '?action=get_products')
          .then(r => r.json())
          .then(list => {
            products = {};
            const tbody = document.querySelector('#productsTable_emb tbody');
            tbody.innerHTML = '';
            const select = document.getElementById('saleProductSelect_emb');
            select.innerHTML = '<option value="">Select product</option>';

            list.forEach((p, idx) => {
              products[p.id] = p;
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${idx + 1}</td>
                <td>${escapeHtml(p.productname)}</td>
                <td>${escapeHtml(p.category)}</td>
                <td>${Number(p.quantity).toLocaleString()}</td>
                <td>${Number(p.purchasing_price).toFixed(2)}</td>
                <td>${Number(p.selling_price).toFixed(2)}</td>
                <td>${p.type}</td>
              `;
              tbody.appendChild(tr);

              const opt = document.createElement('option');
              opt.value = p.id;
              opt.textContent = p.productname;
              select.appendChild(opt);
            });
          })
          .catch(() => {
            Swal.fire('Error', 'Could not load products', 'error');
          });
      }

      // DOM bindings (embedded IDs)
      document.getElementById('openAddProductBtn_emb').addEventListener('click', () => {
        new bootstrap.Modal(document.getElementById('addProductModal_emb')).show();
      });
      document.getElementById('reloadProducts_emb').addEventListener('click', fetchProducts);
      document.getElementById('btnAddSale_emb').addEventListener('click', () => {
        document.getElementById('saleProductSelect_emb').value = '';
        document.getElementById('saleProductDetails_emb').style.display = 'none';
        new bootstrap.Modal(document.getElementById('addSaleModal_emb')).show();
      });

      // Add product submit
      document.getElementById('addProductForm_emb').addEventListener('submit', function (ev) {
        ev.preventDefault();
        const form = ev.target;
        const fd = new FormData(form);
        fd.append('action', 'add_product');

        fetch(apiBase, { method: 'POST', body: fd })
          .then(r => r.json())
          .then(resp => {
            if (resp.success) {
              Swal.fire('Saved', resp.message || 'Product added', 'success');
              form.reset();
              bootstrap.Modal.getInstance(document.getElementById('addProductModal_emb')).hide();
              fetchProducts();
            } else {
              Swal.fire('Error', resp.message || 'Could not save product', 'error');
            }
          })
          .catch(() => Swal.fire('Error', 'Network error', 'error'));
      });

      // Sale product select change
      document.getElementById('saleProductSelect_emb').addEventListener('change', function () {
        const id = this.value;
        if (!id) {
          document.getElementById('saleProductDetails_emb').style.display = 'none';
          return;
        }
        const p = products[id];
        if (!p) return;
        document.getElementById('saleCategory_emb').value = p.category;
        document.getElementById('saleType_emb').value = p.type;
        document.getElementById('saleAvailable_emb').value = Number(p.quantity).toLocaleString();
        document.getElementById('salePrice_emb').value = Number(p.selling_price).toFixed(2);
        document.getElementById('saleQty_emb').value = '';
        document.getElementById('saleTotal_emb').value = '';
        document.getElementById('saleProductDetails_emb').style.display = 'block';

        if (p.type === 'Unit' || p.type === 'Piece' || p.type === 'Kilogram') {
          Swal.fire({
            icon: 'info',
            title: 'Unit requirement',
            text: `This product is of type "${p.type}". Please sell in the specified unit type.`,
            timer: 3000,
            showConfirmButton: false
          });
        }
      });

      // quantity input -> calculate total and validate unit rules
      document.getElementById('saleQty_emb').addEventListener('input', function () {
        const qty = parseFloat(this.value || 0);
        const price = parseFloat(document.getElementById('salePrice_emb').value || 0);
        const type = document.getElementById('saleType_emb').value;
        const available = parseFloat((document.getElementById('saleAvailable_emb').value || '0').replace(/,/g, ''));

        if ((type === 'Unit' || type === 'Piece') && qty && !Number.isInteger(qty)) {
          Swal.fire('Invalid quantity', `Product type "${type}" must be sold in whole numbers`, 'warning');
        }

        if (qty > available) {
          Swal.fire('Insufficient stock', 'Quantity exceeds available stock', 'error');
        }

        const total = (qty * price) || 0;
        document.getElementById('saleTotal_emb').value = total.toFixed(2);
      });

      // submit sale
      document.getElementById('addSaleForm_emb').addEventListener('submit', function (ev) {
        ev.preventDefault();
        const product_id = document.getElementById('saleProductSelect_emb').value;
        const qty = parseFloat(document.getElementById('saleQty_emb').value || 0);
        const total = parseFloat(document.getElementById('saleTotal_emb').value || 0);

        if (!product_id) { Swal.fire('Select product', 'Please choose a product', 'warning'); return; }
        if (qty <= 0) { Swal.fire('Invalid quantity', 'Enter a quantity greater than zero', 'warning'); return; }

        const p = products[product_id];
        if (!p) { Swal.fire('Error', 'Product not found', 'error'); return; }

        if ((p.type === 'Unit' || p.type === 'Piece') && !Number.isInteger(qty)) {
          Swal.fire('Invalid quantity', `Product type "${p.type}" must be sold in whole numbers`, 'warning');
          return;
        }

        if (qty > parseFloat(p.quantity)) {
          Swal.fire('Insufficient stock', 'Not enough quantity available', 'error');
          return;
        }

        const fd = new FormData();
        fd.append('action', 'add_sale');
        fd.append('product_id', product_id);
        fd.append('quantity_sold', qty);
        fd.append('total_price', total);

        fetch(apiBase, { method: 'POST', body: fd })
          .then(r => r.json())
          .then(resp => {
            if (resp.success) {
              Swal.fire('Sale recorded', resp.message || 'Sale added successfully', 'success');
              bootstrap.Modal.getInstance(document.getElementById('addSaleModal_emb')).hide();
              fetchProducts();
            } else {
              Swal.fire('Error', resp.message || 'Could not record sale', 'error');
            }
          })
          .catch(() => Swal.fire('Error', 'Network error', 'error'));
      });

      // initial load
      fetchProducts();
    }

    // expose globally so dashboard can call it if needed
    window.initSaleEmbedded = initSaleEmbedded;

    // If sale.php is opened directly (not embedded), include full page scripts and call init immediately
    if (!document.getElementById('sale-embedded')) {
      // When opened standalone, render full page below (handled by the non-embed branch)
    }
    </script>
    <?php
    exit;
}

// If not embed, render full standalone page (same UI + scripts)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sale Tracker</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body { background:#f4f7fb; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; padding:20px; }
  </style>
</head>
<body>
  <!-- Standalone UI: reuse the same embedded markup -->
  <?php
  // Output the same embedded HTML so standalone and embed share markup
  // We'll include the same fragment as above by calling the embed URL internally
  echo file_get_contents(__FILE__ . '?embed=1');
  ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // When opened standalone the embedded fragment's script defines initSaleEmbedded
    if (window.initSaleEmbedded) {
      initSaleEmbedded();
    }
  </script>
</body>
</html>
