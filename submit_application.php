<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

function text_field(string $name, int $max = 255): string
{
    $value = trim((string) ($_POST[$name] ?? ''));
    if (strlen($value) > $max) {
        throw new InvalidArgumentException("$name is too long.");
    }
    return $value;
}

try {
    $senderName = text_field('sender_name', 150);
    $senderPhone = text_field('sender_phone', 50);
    $senderAddress = text_field('sender_address', 500);
    $receiverName = text_field('receiver_name', 150);
    $receiverPhone = text_field('receiver_phone', 50);
    $receiverAddress = text_field('receiver_address', 500);
    $origin = text_field('origin', 150);
    $destination = text_field('destination', 150);
    $hawbNumber = text_field('hawb_number', 100);
    $shipmentDate = text_field('shipment_date', 10);
    $pieces = filter_var($_POST['pieces'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $weightKg = filter_var($_POST['weight_kg'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
    $dimensions = text_field('dimensions', 100);
    $paymentTerms = text_field('payment_terms', 50);
    $cargoDescription = text_field('cargo_description', 2000);
    $specialInstructions = text_field('special_instructions', 2000);

    if (!$senderName || !$senderPhone || !$senderAddress || !$receiverName || !$receiverPhone ||
        !$receiverAddress || !$origin || !$destination || !$shipmentDate || $pieces === false ||
        $weightKg === false || !$paymentTerms || !$cargoDescription || empty($_POST['consent'])) {
        throw new InvalidArgumentException('Please complete all required fields.');
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $shipmentDate)) {
        throw new InvalidArgumentException('Invalid shipment date.');
    }

    $allowedTerms = ['Prepaid', 'Collect', 'Other'];
    if (!in_array($paymentTerms, $allowedTerms, true)) {
        throw new InvalidArgumentException('Invalid payment terms.');
    }

    $reference = 'HAWB-' . strtoupper(bin2hex(random_bytes(5)));

    $sql = 'INSERT INTO cargo_applications
        (reference, hawb_number, sender_name, sender_phone, sender_address,
         receiver_name, receiver_phone, receiver_address, origin, destination,
         shipment_date, pieces, weight_kg, dimensions, cargo_description,
         payment_terms, special_instructions, consent_given)
        VALUES
        (:reference, :hawb_number, :sender_name, :sender_phone, :sender_address,
         :receiver_name, :receiver_phone, :receiver_address, :origin, :destination,
         :shipment_date, :pieces, :weight_kg, :dimensions, :cargo_description,
         :payment_terms, :special_instructions, :consent_given)';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'reference' => $reference,
        'hawb_number' => $hawbNumber !== '' ? $hawbNumber : null,
        'sender_name' => $senderName,
        'sender_phone' => $senderPhone,
        'sender_address' => $senderAddress,
        'receiver_name' => $receiverName,
        'receiver_phone' => $receiverPhone,
        'receiver_address' => $receiverAddress,
        'origin' => $origin,
        'destination' => $destination,
        'shipment_date' => $shipmentDate,
        'pieces' => $pieces,
        'weight_kg' => $weightKg,
        'dimensions' => $dimensions !== '' ? $dimensions : null,
        'cargo_description' => $cargoDescription,
        'payment_terms' => $paymentTerms,
        'special_instructions' => $specialInstructions !== '' ? $specialInstructions : null,
        'consent_given' => 1,
    ]);

    header('Location: application.php?success=1&reference=' . rawurlencode($reference));
    exit;
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
} catch (Throwable $e) {
    error_log('Cargo application submission failed: ' . $e->getMessage());
    http_response_code(500);
    exit('The application could not be submitted. Please try again.');
}
