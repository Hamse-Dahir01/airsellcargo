<?php
// Application form for cargo shipment / HAWB submissions.
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Air Cargo Shipment / HAWB Application</title>
  <link rel="stylesheet" href="styles.css">
</head> 
<body>
  <main class="quote-section" style="min-height:100vh">
    <div class="quote-wrap">
      <div class="quote-intro">
        <p class="eyebrow">AIRSELL CARGO</p>
        <h2>Shipment / <em>HAWB application</em></h2>
      </div>

      <form class="quote-form" action="submit_application.php" method="post">
        <div class="form-heading"><span>CARGO APPLICATION</span><span>* REQUIRED</span></div>

        <div class="form-row">
          <label>Sender name *<input name="sender_name" autocomplete="name" required></label>
          <label>Sender phone *<input name="sender_phone" autocomplete="tel" required></label>
        </div>
        <label class="full-label">Sender address *<input name="sender_address" autocomplete="street-address" required></label>

        <div class="form-row">
          <label>Receiver name *<input name="receiver_name" required></label>
          <label>Receiver phone *<input name="receiver_phone" autocomplete="tel" required></label>
        </div>
        <label class="full-label">Receiver address *<input name="receiver_address" required></label>

        <div class="form-row">
          <label>Origin / airport *<input name="origin" required></label>
          <label>Destination / airport *<input name="destination" required></label>
        </div>
        <div class="form-row">
          <label>HAWB number <input name="hawb_number" maxlength="100"></label>
          <label>Shipment date *<input name="shipment_date" type="date" required></label>
        </div>

        <div class="form-row">
          <label>Number of pieces *<input name="pieces" type="number" min="1" required></label>
          <label>Gross weight (kg) *<input name="weight_kg" type="number" min="0.01" step="0.01" required></label>
        </div>
        <div class="form-row">
          <label>Dimensions (cm)<input name="dimensions" placeholder="L × W × H"></label>
          <label>Payment terms *
            <select name="payment_terms" required>
              <option value="" disabled selected>Select one</option>
              <option>Prepaid</option>
              <option>Collect</option>
              <option>Other</option>
            </select>
          </label>
        </div>

        <label class="full-label">Cargo description *<textarea name="cargo_description" rows="3" required></textarea></label>
        <label class="full-label">Special handling instructions<textarea name="special_instructions" rows="3"></textarea></label>
        <label class="consent"><input type="checkbox" name="consent" value="1" required><span>I confirm that the shipment information is accurate and agree to be contacted. *</span></label>

        <button type="submit" class="button button-dark submit-button">Submit cargo application</button>
      </form>
    </div>
  </main>
</body>
</html>
