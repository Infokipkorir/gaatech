<?php
session_start();
require_once "db.php";
include "loader.php";

// Load shop settings
$shop = $conn->query("SELECT * FROM shop_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();

// Load staff
$staff_result = $conn->query("SELECT * FROM staff");

// Load default items
$item_result = $conn->query("SELECT * FROM shop_items");

// Include QR code library (use composer: endroid/qr-code)
use Endroid\QrCode\Builder\Builder;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt Marker</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f8fafc; font-family:'Segoe UI', sans-serif; }
.container { max-width:900px; margin:30px auto; }
.card { padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); }
.item-row input { width:100px; }
.receipt { background:white; padding:20px; border-radius:12px; margin-top:20px; }
.receipt-header { display:flex; align-items:center; justify-content:space-between; }
.receipt-logo { max-width:80px; }
.qr-code { text-align:center; margin-top:15px; }
@media(max-width:600px){ .receipt-header { flex-direction:column; align-items:center; gap:10px; } }
</style>
</head>
<body>
<div class="container">
<h2 class="mb-4 text-center">Receipt Marker</h2>

<div class="card">
    <form id="receiptForm">
        <!-- Shop Header -->
        <div class="mb-3">
            <strong>Shop:</strong> <input type="text" class="form-control mb-2" value="<?= htmlspecialchars($shop['shop_name'] ?? '') ?>" id="shopName">
            <input type="text" class="form-control mb-2" value="<?= htmlspecialchars($shop['tagline'] ?? '') ?>" id="tagline">
            <input type="text" class="form-control mb-2" value="<?= htmlspecialchars($shop['branch'] ?? '') ?>" id="branch">
        </div>

        <!-- Staff -->
        <div class="mb-3">
            <label>Staff:</label>
            <select class="form-select" id="staff">
                <?php while($staff = $staff_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($staff['name']) ?>"><?= htmlspecialchars($staff['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Items -->
        <div class="mb-3">
            <h5>Items</h5>
            <table class="table table-bordered" id="itemsTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th><button type="button" class="btn btn-sm btn-success" id="addItemBtn">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $item_result->fetch_assoc()): ?>
                    <tr class="item-row">
                        <td><input type="text" class="form-control item-name" value="<?= htmlspecialchars($item['name']) ?>"></td>
                        <td>
                            <select class="form-select unit">
                                <option value="pcs" <?= $item['unit']=='pcs'?'selected':'' ?>>pcs</option>
                                <option value="Kg" <?= $item['unit']=='Kg'?'selected':'' ?>>Kg</option>
                                <option value="g" <?= $item['unit']=='g'?'selected':'' ?>>g</option>
                                <option value="L" <?= $item['unit']=='L'?'selected':'' ?>>L</option>
                                <option value="ml" <?= $item['unit']=='ml'?'selected':'' ?>>ml</option>
                            </select>
                        </td>
                        <td><input type="number" class="form-control qty" value="1" min="0"></td>
                        <td><input type="number" class="form-control price" value="<?= $item['price'] ?>" step="0.01"></td>
                        <td><input type="text" class="form-control total" readonly></td>
                        <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">x</button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Discount & Tax -->
        <div class="mb-3 d-flex gap-2">
            <input type="number" class="form-control" id="discount" placeholder="Discount %" value="0">
            <input type="number" class="form-control" id="tax" placeholder="Tax %" value="<?= $shop['tax_rate'] ?? 0 ?>">
        </div>

        <button type="button" class="btn btn-primary w-100" id="generateReceipt">Generate Receipt</button>
    </form>
</div>

<!-- Receipt Preview -->
<div class="receipt mt-4" id="receiptPreview" style="display:none;">
    <div class="receipt-header">
        <div>
            <img src="<?= $shop['logo'] ?? '' ?>" class="receipt-logo">
            <h4 id="rShopName"></h4>
            <small id="rTagline"></small><br>
            <small id="rBranch"></small>
        </div>
        <div>
            <p>Staff: <span id="rStaff"></span></p>
            <p>Date: <span id="rDate"></span></p>
            <p>Receipt #: <span id="rSerial"></span></p>
        </div>
    </div>
    <hr>
    <table class="table" id="rItemsTable">
        <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Price</th><th>Total</th></tr></thead>
        <tbody></tbody>
    </table>
    <hr>
    <p>Subtotal: $<span id="rSubtotal">0.00</span></p>
    <p>Tax: $<span id="rTax">0.00</span></p>
    <p>Discount: $<span id="rDiscount">0.00</span></p>
    <h4>Total: $<span id="rTotal">0.00</span></h4>

    <div class="qr-code" id="rQRCode"></div>
    <button class="btn btn-success w-100 mt-3" onclick="window.print()">Print / Download</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/uuid/9.0.0/uuid.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
function calculateTotals(){
    let subtotal=0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(row=>{
        const qty=parseFloat(row.querySelector('.qty').value)||0;
        const price=parseFloat(row.querySelector('.price').value)||0;
        const total=qty*price;
        row.querySelector('.total').value=total.toFixed(2);
        subtotal+=total;
    });
    return subtotal;
}

document.querySelectorAll('#itemsTable tbody').forEach(tbody=>{
    tbody.addEventListener('input', calculateTotals);
});

document.getElementById('addItemBtn').addEventListener('click',()=>{
    const tbody=document.querySelector('#itemsTable tbody');
    const tr=document.createElement('tr');
    tr.classList.add('item-row');
    tr.innerHTML=`<td><input type="text" class="form-control item-name"></td>
    <td><select class="form-select unit"><option value="pcs">pcs</option><option value="Kg">Kg</option><option value="g">g</option><option value="L">L</option><option value="ml">ml</option></select></td>
    <td><input type="number" class="form-control qty" value="1" min="0"></td>
    <td><input type="number" class="form-control price" value="0" step="0.01"></td>
    <td><input type="text" class="form-control total" readonly></td>
    <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">x</button></td>`;
    tbody.appendChild(tr);
});

document.addEventListener('click', e=>{
    if(e.target.classList.contains('removeItemBtn')){
        e.target.closest('tr').remove();
        calculateTotals();
    }
});

document.getElementById('generateReceipt').addEventListener('click',()=>{
    const receipt=document.getElementById('receiptPreview');
    receipt.style.display='block';
    document.getElementById('rShopName').textContent=document.getElementById('shopName').value;
    document.getElementById('rTagline').textContent=document.getElementById('tagline').value;
    document.getElementById('rBranch').textContent=document.getElementById('branch').value;
    document.getElementById('rStaff').textContent=document.getElementById('staff').value;
    document.getElementById('rDate').textContent=new Date().toLocaleString();
    document.getElementById('rSerial').textContent=uuid.v4().slice(0,8);

    const tbody=document.querySelector('#rItemsTable tbody');
    tbody.innerHTML='';
    let subtotal=0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(row=>{
        const item=row.querySelector('.item-name').value;
        const qty=row.querySelector('.qty').value;
        const unit=row.querySelector('.unit').value;
        const price=parseFloat(row.querySelector('.price').value)||0;
        const total=qty*price;
        subtotal+=total;
        tbody.innerHTML+=`<tr><td>${item}</td><td>${qty}</td><td>${unit}</td><td>${price.toFixed(2)}</td><td>${total.toFixed(2)}</td></tr>`;
    });

    let tax=parseFloat(document.getElementById('tax').value)||0;
    let discount=parseFloat(document.getElementById('discount').value)||0;
    let taxAmount=subtotal*(tax/100);
    let discountAmount=subtotal*(discount/100);
    let total=subtotal+taxAmount-discountAmount;

    document.getElementById('rSubtotal').textContent=subtotal.toFixed(2);
    document.getElementById('rTax').textContent=taxAmount.toFixed(2);
    document.getElementById('rDiscount').textContent=discountAmount.toFixed(2);
    document.getElementById('rTotal').textContent=total.toFixed(2);

    // Generate QR code (using QR code API)
    const qrText=JSON.stringify({
        shop: document.getElementById('shopName').value,
        date: new Date().toLocaleString(),
        staff: document.getElementById('staff').value,
        total: total.toFixed(2)
    });
    document.getElementById('rQRCode').innerHTML=`<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(qrText)}">`;
});
</script>
</body>
</html>
