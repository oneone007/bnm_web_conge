<?php
// Headers
header("Content-Type: text/html; charset=UTF-8");

// Get parameters if they exist
$bank = isset($_GET['bank']) ? htmlspecialchars($_GET['bank']) : '';
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$amountText = isset($_GET['amountText']) ? htmlspecialchars($_GET['amountText']) : '';
$amountTextLine2 = isset($_GET['amountTextLine2']) ? htmlspecialchars($_GET['amountTextLine2']) : '';
$date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : '';
$place = isset($_GET['place']) ? htmlspecialchars($_GET['place']) : '';
$payTo = isset($_GET['payTo']) ? htmlspecialchars($_GET['payTo']) : '';

// Load positions from coordinates.json (for printing)
$positionsJson = file_get_contents('coordinates.json');
$positions = json_decode($positionsJson, true);

// If bank doesn't exist in positions or no bank is selected, use default positions
if (!isset($positions[$bank])) {
    $positions = [
        'amount' => ['top' => 100, 'left' => 600],
        'amountText' => ['top' => 140, 'left' => 200],
        'amountTextLine2' => ['top' => 160, 'left' => 200], // Default position for line 2
        'date' => ['top' => 170, 'left' => 550],
        'place' => ['top' => 170, 'left' => 450],
        'payTo' => ['top' => 140, 'left' => 350]
    ];
} else {
    $positions = $positions[$bank];
    
    // If amountTextLine2 position doesn't exist, create a default one
    if (!isset($positions['amountTextLine2'])) {
        $positions['amountTextLine2'] = [
            'top' => $positions['amountText']['top'] + 20,
            'left' => $positions['amountText']['left']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Print</title>
    <style>
        @page {
            size: 9.5in 4.125in; /* Envelope #10 size */
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: white;
            position: relative;
            font-family: Arial, sans-serif;
            font-size: 14px;
            width: 800px;
            height: 400px;
        }
        
        .print-container {
            position: relative;
            width: 800px;
            height: 400px;
            margin: 0 auto;
        }
        
        .print-element {
            position: absolute;
            font-size: 14px;
            color: black;
            line-height: 1.5;
        }
        
        .amount {
            font-weight: bold;
        }
        
        .amount-text {
            font-weight: bold;
            max-width: 600px;
            font-size: 14px;
            white-space: pre-wrap;
            word-wrap: normal;
            word-break: keep-all;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        
        .print-controls {
            text-align: center;
            margin: 20px;
            padding: 10px;
            background-color: #f8f8f8;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        button {
            padding: 10px 20px;
            background-color: #0078d7;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
            font-size: 16px;
        }
        
        @media print {
            @page {
                size: 9.5in 4.125in; /* Envelope #10 size */
                margin: 0;
            }
            
            .print-controls {
                display: none;
            }
            
            body {
                width: 100%;
                height: 100%;
            }
            
            .print-container {
                width: 100%;
                height: 100%;
            }
            
            /* Ensure elements are positioned exactly as specified */
            .print-element {
                position: absolute !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <?php if (!empty($amount)): ?>
        <div class="print-element amount" style="top: <?php echo $positions['amount']['top']; ?>px; left: <?php echo $positions['amount']['left']; ?>px;">
            <?php echo number_format($amount, 2, '.', ','); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($amountText)): ?>
        <div class="print-element amount-text" style="top: <?php echo $positions['amountText']['top']; ?>px; left: <?php echo $positions['amountText']['left']; ?>px;">
            <?php echo $amountText; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($amountTextLine2)): ?>
        <div class="print-element amount-text" style="top: <?php echo $positions['amountTextLine2']['top']; ?>px; left: <?php echo $positions['amountTextLine2']['left']; ?>px;">
            <?php echo $amountTextLine2; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($date)): ?>
        <div class="print-element" style="top: <?php echo $positions['date']['top']; ?>px; left: <?php echo $positions['date']['left']; ?>px;">
            <?php echo $date; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($place)): ?>
        <div class="print-element" style="top: <?php echo $positions['place']['top']; ?>px; left: <?php echo $positions['place']['left']; ?>px;">
            <?php echo $place; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($payTo)): ?>
        <div class="print-element" style="top: <?php echo $positions['payTo']['top']; ?>px; left: <?php echo $positions['payTo']['left']; ?>px;">
            <?php echo $payTo; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="print-controls">
        <button onclick="window.print();">Print Check</button>
        <button onclick="window.close();">Close</button>
    </div>
</body>
</html>
