<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AJAX Save</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        button { padding: 8px 16px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-success { background: #10b981; color: white; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Test AJAX Save Functionality</h1>
    
    <div class="test-section">
        <h2>Test "Save as Pending" AJAX Endpoint</h2>
        <p>This will test the save_inventory_draft.php endpoint with sample data.</p>
        <button class="btn-primary" onclick="testSavePending()">Test Save as Pending</button>
        <div id="save-result"></div>
    </div>
    
    <div class="test-section">
        <h2>Test Inventory List</h2>
        <p>This will fetch the inventory list from the admin page.</p>
        <button class="btn-success" onclick="testInventoryList()">Test Inventory List</button>
        <div id="list-result"></div>
    </div>

    <script>
        async function testSavePending() {
            const resultDiv = document.getElementById('save-result');
            resultDiv.innerHTML = '<p class="info">Testing save functionality...</p>';
            
            const testData = {
                title: 'AJAX Test Inventory - ' + new Date().toLocaleString(),
                notes: 'This is a test inventory created via AJAX',
                entryItems: [
                    {
                        product_name: 'Test Product A',
                        quantity: 150,
                        date: '2024-01-20',
                        lot: 'AJAX001',
                        ppa: 22.50,
                        qty_dispo: 75
                    },
                    {
                        product_name: 'Test Product B',
                        quantity: 300,
                        date: '2024-01-21',
                        lot: 'AJAX002',
                        ppa: 18.25,
                        qty_dispo: 200
                    }
                ],
                sortieItems: [
                    {
                        product_name: 'Test Product C',
                        quantity: 80,
                        date: '2024-01-22',
                        lot: 'AJAX003',
                        ppa: 35.00,
                        qty_dispo: 40
                    }
                ]
            };
            
            try {
                const response = await fetch('save_inventory_draft.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.innerHTML = `
                        <p class="success">✅ Save successful!</p>
                        <p><strong>Inventory ID:</strong> ${result.inventory_id}</p>
                        <p><strong>Message:</strong> ${result.message}</p>
                        <details>
                            <summary>Request Data</summary>
                            <pre>${JSON.stringify(testData, null, 2)}</pre>
                        </details>
                        <details>
                            <summary>Response Data</summary>
                            <pre>${JSON.stringify(result, null, 2)}</pre>
                        </details>
                        <p><a href="inv_admin.php" style="background: #3b82f6; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px;">View in Admin</a></p>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <p class="error">❌ Save failed!</p>
                        <p><strong>Error:</strong> ${result.message}</p>
                        <details>
                            <summary>Response Data</summary>
                            <pre>${JSON.stringify(result, null, 2)}</pre>
                        </details>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <p class="error">❌ Network error!</p>
                    <p><strong>Error:</strong> ${error.message}</p>
                `;
            }
        }
        
        async function testInventoryList() {
            const resultDiv = document.getElementById('list-result');
            resultDiv.innerHTML = '<p class="info">Fetching inventory list...</p>';
            
            try {
                const response = await fetch('inv_admin.php');
                
                if (response.ok) {
                    resultDiv.innerHTML = `
                        <p class="success">✅ Admin page loads successfully!</p>
                        <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                        <p><strong>Content-Type:</strong> ${response.headers.get('content-type')}</p>
                        <p><a href="inv_admin.php" target="_blank" style="background: #10b981; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px;">Open Admin Page</a></p>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <p class="error">❌ Admin page error!</p>
                        <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <p class="error">❌ Network error!</p>
                    <p><strong>Error:</strong> ${error.message}</p>
                `;
            }
        }
    </script>
</body>
</html>
