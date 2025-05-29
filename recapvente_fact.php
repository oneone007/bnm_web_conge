<?php
session_start();



// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}



if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente'])) {
    header("Location: Acess_Denied");    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R Vente Facturation</title>
    <script src="main.js" defer></script>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="recap_achat.css">
    <script src="theme.js"></script>




</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
 





    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script>
        lottie.loadAnimation({
            container: document.getElementById("lottieContainer"),
            renderer: "svg",
            loop: true,
            autoplay: true,
            path: "json_files/r.json" // Replace with actual path to your .rjson file
        });
    </script>



</script>



    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">

    <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Reacap Vente Facturation
            </h1>
        </div>

        <!-- Filters -->


        <br>



        <!-- Search Fields -->
<!-- Search Fields -->
<!-- Search Fields -->



        <br>
        <!-- Date Inputs -->
        <div class="date-container flex space-x-4 items-center">
    <div class="flex items-center space-x-2">
        <label for="start-date">Begin Date:</label>
        <input type="date" id="start-date" class="border rounded px-2 py-1">
    </div>

    <div class="flex items-center space-x-2">
        <label for="end-date">End Date:</label>
        <input type="date" id="end-date" class="border rounded px-2 py-1">
    </div>

    <!-- Refresh Button with Icon -->
    <button id="refresh-btn" class="p-3 bg-white text-blue-500 rounded-full shadow-lg hover:shadow-xl border border-blue-500 transition duration-200 flex items-center justify-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
    </svg>
</button>

</div>


        <br>

        <!-- <button id="downloadExcel_totalrecap"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Total Recap Download</span>
        </button> -->

        <div class="container">
  <button id="downloadExcel_totalrecap" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
</div>




        <br>
        
        <!-- Table -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> Total Recap</h2>

                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">Date</th>
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">CHIFFRE</th>
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">QTY</th>
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">MARGE</th>
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">POURCENTAGE</th>
                        </tr>
                    </thead>
                    <tbody id="recap-table" class="dark:bg-gray-800">
                        <tr id="loading-row">
                            <td colspan="5" class="text-center p-4">
                                <div id="lottie-container" style="width: 290px; height: 200px; margin: auto;"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>






     

        <!-- second table remise aauto  -->


        <!-- Pagination -->
        <div class="mt-4 flex justify-center space-x-2" id="pagination"></div>
        
        <div class="download-wrapper">

            <!-- <button id="download-fournisseur"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Fournisseur Download </span>
            </button> -->
            <button id="download-fournisseur" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>  <button id="download-product-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
             <!-- <button id="download-product-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Product Download</span>
            </button> -->
        </div>

   
     
        <div class="table-wrapper">
            <!-- First Table -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">RECAP PAR FOURNISSEUR</h2>
                    <div>
                      <input type="text" id="recap_fournisseur" placeholder="Search..." class="p-2 border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:placeholder-gray-400">
                    </div>
                </div>
        
                <div class="overflow-x-auto">

                

                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="FOURNISSEUR" onclick="sortrecapTable('FOURNISSEUR')"
                                    class="border px-4 py-2">Fournisseur</th>
                                <th data-column="Total" onclick="sortrecapTable('Total')" class="border px-4 py-2">Total
                                </th>
                                <th data-column="QTy" onclick="sortrecapTable('QTy')" class="border px-4 py-2">
                                    QTy</th>
                                <th data-column="Marge" onclick="sortrecapTable('Marge')" class="border px-4 py-2">
                                    Marge</th>
                               
                            </tr>
                        </thead>
                        <tbody id="recap-frnsr-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="5" class="text-center p-4">
                                    <div id="lottie-container-d" style="width: 290px; height: 200px; margin: auto;">
                                    </div>
                                </td>
                            </tr>


                        </tbody>
                    </table>


                </div>
                <div id="pagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="firstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="prevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="pageIndicator"></span>
    <button id="nextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="lastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>            </div>


            <!-- Second Table -->

            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                   
                    <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                        <h2 class="text-lg font-semibold dark:text-black">RECAP PAR PRODUIT</h2>
                        <div>
                          <input type="text" id="recap_product" placeholder="Search..." class="p-2 border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:placeholder-gray-400">
                        </div>
                    </div>
                <div class="overflow-x-auto">


                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="PRODUCT" onclick="sortrecpproductTable('PRODUCT')"
                                    class="border px-4 py-2">
                                    Product</th>
                                <th data-column="Total" onclick="sortrecpproductTable('Total')"
                                    class="border px-4 py-2">Total
                                </th>
                                <th data-column="QTY" onclick="sortrecpproductTable('QTY')" class="border px-4 py-2">QTY
                                </th>
                                <th data-column="Marge" onclick="sortrecpproductTable('Marge')"
                                    class="border px-4 py-2">Marge
                                </th>
                            </tr>
                        </thead>
                        <tbody id="recap-prdct-table" class="dark:bg-gray-800"></tbody>
                        <tr id="loading-row">
                            <td colspan="5" class="text-center p-4">
                                <div id="lottie-d" style="width: 290px; height: 200px; margin: auto;"></div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="productPagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="firstProductPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="prevProductPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="productPageIndicator"></span>
    <button id="nextProductPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="lastProductPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>
           </div>
        </div>
        <script>
function makeTableColumnsResizable(table) {
    const cols = table.querySelectorAll("th");
    const tableContainer = table.parentElement;

    cols.forEach((col) => {
        // Create a resizer handle
        const resizer = document.createElement("div");
        resizer.classList.add("resizer");
        col.style.position = "relative";
        resizer.style.width = "5px";
        resizer.style.height = "100%";
        resizer.style.position = "absolute";
        resizer.style.top = "0";
        resizer.style.right = "0";
        resizer.style.cursor = "col-resize";
        resizer.style.userSelect = "none";
        resizer.style.zIndex = "10";

        col.appendChild(resizer);

        let x = 0;
        let w = 0;

        resizer.addEventListener("mousedown", (e) => {
            x = e.clientX;
            w = col.offsetWidth;

            document.addEventListener("mousemove", mouseMoveHandler);
            document.addEventListener("mouseup", mouseUpHandler);
        });

        const mouseMoveHandler = (e) => {
            const dx = e.clientX - x;
            col.style.width = `${w + dx}px`;
        };

        const mouseUpHandler = () => {
            document.removeEventListener("mousemove", mouseMoveHandler);
            document.removeEventListener("mouseup", mouseUpHandler);
        };
    });
}

// Wait for the DOM to load before applying resizable
document.addEventListener("DOMContentLoaded", () => {
    const tables = document.querySelectorAll(".table-container table");
tables.forEach((table) => makeTableColumnsResizable(table));
});
</script>

        <div class="download-wrapper">

            <!-- <button id="download-zone-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Zone Download </span> -->


                <button id="download-zone-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>  <button id="download-client-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
                
            <!-- </button> <button id="download-client-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Client Download</span>
            </button> -->
        </div>

        <div class="table-wrapper">
            <!-- First Table: RECAP PAR ZONE -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
              

                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">RECAP PAR ZONE</h2>
                    <img id="generate-chart-zone"
                    src="assets/chrt.png"
                    alt="chart Icon"
                    class="w-6 h-6 cursor-pointer transform hover:scale-105 transition-all duration-300 ease-in-out"
                    >
                    <div>
                     
                                               <input type="text" id="recap_zone" placeholder="Search..." class="p-2 border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:placeholder-gray-400">
                    </div>
                </div>
            
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="ZONE" onclick="sortrecapzone('ZONE')" class="border px-4 py-2">NAME</th>
                                <th data-column="Total" onclick="sortrecapzone('Total')" class="border px-4 py-2">Total</th>
                                <th data-column="QTy" onclick="sortrecapzone('QTy')" class="border px-4 py-2">QTy</th>
                                <th data-column="Marge" onclick="sortrecapzone('Marge')" class="border px-4 py-2">Marge</th>
                            </tr>
                        </thead>
                        <tbody id="recap-zone-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="4" class="text-center p-4">
                                    <div id="zone" style="width: 290px; height: 200px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="zonePagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="firstzonePage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="prevzonePage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="zonePageIndicator"></span>
    <button id="nextzonePage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="lastzonePage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>

            </div>
            

            <!-- Second Table: RECAP CLIENT -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">RECAP CLIENT</h2>
                    <div>
                   <input type="text" id="recap_client" placeholder="Search..." class="p-2 border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:placeholder-gray-400">
                    </div>
                </div>  
                              <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="CLIENT" onclick="sortrecpclienttTable('CLIENT')"
                                    class="border px-4 py-2">NAME</th>
                                <th data-column="TOTAL" onclick="sortrecpclienttTable('TOTAL')"
                                    class="border px-4 py-2">Total</th>
                                <th data-column="QTY" onclick="sortrecpclienttTable('QTY')" class="border px-4 py-2">QTy
                                </th>
                                <th data-column="MARGE" onclick="sortrecpclienttTable('MARGE')"
                                    class="border px-4 py-2">Marge</th>
                            </tr>
                        </thead>
                        <tbody id="recap-client-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="4" class="text-center p-4">
                                    <div id="client" style="width: 290px; height: 200px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="clpagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="clfirstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="clprevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="clpageIndicator"></span>
    <button id="clnextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="cllastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>
            </div>
        </div>
     
        <div class="download-wrapper">

            <!-- <button id="download-Opérateur-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Opérateur Download </span>
             -->

                <button id="download-Opérateur-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>  <button id="download-BCCB-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>

                
            <!-- </button> <button id="download-BCCB-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>BCCB Download</span>
            </button> -->
        </div>
    
        <div class="table-wrapper flex flex-wrap gap-6">
            <!-- First Table: RECAP PAR OPÉRATEUR -->
            <div class="table-container flex-1 min-w-[400px] rounded-lg bg-white shadow-md dark:bg-gray-800">
            
                

                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">RECAP PAR OPÉRATEUR</h2>
                    <img id="generate-chart-operateur"
                    src="assets/chrt.png"
                    alt="chart Icon"
                    class="w-6 h-6 cursor-pointer transform hover:scale-105 transition-all duration-300 ease-in-out"
                    >
                    <div>
                     
                             <input type="text" id="recap_operateur" placeholder="Search..." class="p-2 border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:placeholder-gray-400">
                    </div>
                </div>

            
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="OPERATEUR" onclick="sortRecapOperator('OPERATEUR')" class="border px-4 py-2">Opérateur</th>
                                <th data-column="TOTAL" onclick="sortRecapOperator('TOTAL')" class="border px-4 py-2">Total</th>
                                <th data-column="QTY" onclick="sortRecapOperator('QTY')" class="border px-4 py-2">QTy</th>
                                <th data-column="MARGE" onclick="sortRecapOperator('MARGE')" class="border px-4 py-2">Marge</th>
                            </tr>
                        </thead>
                        <tbody id="recap-operator-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="4" class="text-center p-4">
                                    <div id="operator" style="width: 290px; height: 200px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="oppagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="opfirstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="opprevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="oppageIndicator"></span>
    <button id="opnextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="oplastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>

            </div>
            

            <!-- Second Table: RECAP PAR BCCB -->
            <div class="table-container flex-1 min-w-[400px] rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">BCCB CLIENT</h2>
                    <div>
                     <input type="text" id="recap_bccbclient" placeholder="Search..." class="p-2 border border-gray-300 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:placeholder-gray-400">
                    </div>
                </div>  
                <div class="overflow-x-auto">
              
  <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="DOCUMENTNO" onclick="sortRecapBccb('DOCUMENTNO')"
                                    class="border px-4 py-2">Document No</th>
                                <th data-column="DATEORDERED" onclick="sortRecapBccb('DATEORDERED')"
                                    class="border px-4 py-2">Date Order</th>
                                <th data-column="GRANDTOTAL" onclick="sortRecapBccb('GRANDTOTAL')"
                                    class="border px-4 py-2">Grand Total</th>
                                <th data-column="MARGE" onclick="sortRecapBccb('MARGE')"
                                    class="border px-4 py-2">Marge (%)</th>
                            </tr>
                        </thead>
                        <tbody id="recap-bccb-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="4" class="text-center p-4">
                                    <div id="bccb" style="width: 290px; height: 200px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
                <div id="bpagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="bfirstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="bprevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="bpageIndicator"></span>
    <button id="bnextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="blastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>
            </div>
        </div>

<br>
<!-- <button id="download-bccb-product-excel"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>BCCB Product Recap Download</span>
        </button> -->
        <div class="container">
  <button id="download-bccb-product-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
</div>
        <div id="bccb-product-container" class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800" style="display: none;">
    <div class="overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">BCCB Product Recap</h2>
        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
            <thead>
                <tr class="table-header dark:bg-gray-700">
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">PRODUCT</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">QTY</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">REMISE</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">MARGE</th>
                </tr>
            </thead>
            <tbody id="recap-bccb-product-table" class="dark:bg-gray-800">
                <tr id="loading-row">
                    <td colspan="4" class="text-center p-4">
                        <div id="lottie-container" style="width: 290px; height: 200px; margin: auto;"></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>




        
        <!-- Chart container -->
        <div style="width: 80%; margin: auto;">
            <canvas id="allcharts" style="width: 100%; height: 400px;"></canvas>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
     


        <br><br><br> <br>
        <script>



            // Define an array of element IDs and their corresponding JSON file paths
            const lottieElements = [
                { id: "lottie-container", path: "json_files/date.json" },
                { id: "lottie-container-d", path: "json_files/l.json" },
                { id: "lottie-d", path: "json_files/l.json" },
                { id: "bccb", path: "json_files/l.json" },
                { id: "operator", path: "json_files/l.json" },
                { id: "zone", path: "json_files/l.json" },
                { id: "client", path: "json_files/l.json" }
            ];

            // Loop through each element and initialize Lottie animation
            lottieElements.forEach(({ id, path }) => {
                const container = document.getElementById(id);
                if (container) {
                    lottie.loadAnimation({
                        container: container,
                        renderer: "svg",
                        loop: true,
                        autoplay: true,
                        path: path
                    });
                }
            });

            // Ensure dates clear on refresh
            window.onload = () => {
    // Clear all inputs except date fields on page load
    document.getElementById("recap_fournisseur").value = "";
    document.getElementById("recap_product").value = "";
    document.getElementById("recap_zone").value = "";
    document.getElementById("recap_client").value = "";
    document.getElementById("recap_operateur").value = "";
    document.getElementById("recap_bccbclient").value = "";

    const startDateInput = document.getElementById("start-date");
    const endDateInput = document.getElementById("end-date");

    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split("T")[0];

    function updateEndDate() {
        if (!endDateInput.value || new Date(endDateInput.value) < new Date(startDateInput.value)) {
            endDateInput.value = today;
        }

        // Trigger events
        endDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("change", { bubbles: true }));
    }

    // Set end date when start date is selected
    startDateInput.addEventListener("change", updateEndDate);

    // Refresh button: clear other fields but keep date fields
    document.getElementById("refresh-btn").addEventListener("click", () => {
        // Clear non-date fields
        document.getElementById("recap_fournisseur").value = "";
        document.getElementById("recap_product").value = "";
        document.getElementById("recap_zone").value = "";
        document.getElementById("recap_client").value = "";
        document.getElementById("recap_operateur").value = "";
        document.getElementById("recap_bccbclient").value = "";

        // Trigger update events for date fields
        startDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        startDateInput.dispatchEvent(new Event("change", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("change", { bubbles: true }));

        // Refresh data based on existing date values
        fetchData(startDateInput.value, endDateInput.value, "", "", "", "", "", ""); 
    });
};



            // Fetch data when both dates are selected
            async function fetchTotalRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;

                if (!startDate || !endDate) return; // Don't fetch until both dates are selected

                try {
                    
                    const response = await fetch(`http://192.168.1.94:5000/fetchTotalrecapData?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000012`);

                    if (!response.ok) throw new Error("Network response was not ok");

                    const data = await response.json();
                    updateTotalRecapTable(data, startDate, endDate);
                    hideLoader();
                } catch (error) {
                    console.error("Error fetching total recap data:", error);
                    document.getElementById('loading-row').innerHTML = "<td colspan='5' class='text-center text-red-500'>Failed to load data</td>";
                    hideLoader();
                }
            }

            function hideLoader() {
                const loaderRow = document.getElementById('loading-row');
                if (loaderRow) {
                    loaderRow.remove();
                }
            }

            // Format number with thousand separators & two decimals
            function formatNumbert(value) {
                if (value === null || value === undefined || isNaN(value)) return "";
                return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Format percentage
            function formatPercentage(value) {
                if (value === null || value === undefined || isNaN(value)) return "";
                return (parseFloat(value) * 100).toFixed(2) + "%";
            }

            // Update table with fetched data
            function updateTotalRecapTable(data, startDate, endDate) {
                const tableBody = document.getElementById("recap-table");
                tableBody.innerHTML = "";

                if (!data || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4">No data available</td></tr>`;
                    return;
                }

                const row = data[0]; // Since it's only one row
                tableBody.innerHTML = `
<tr class="dark:bg-gray-700">
    <td class="border px-4 py-2 dark:border-gray-600">From ${startDate} to ${endDate}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.CHIFFRE)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.QTY)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.MARGE)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatPercentage(row.POURCENTAGE)}</td>
</tr>
`;
            }

            // Attach event listeners to date inputs
            document.getElementById("start-date").addEventListener("change", fetchTotalRecap);
            document.getElementById("end-date").addEventListener("change", fetchTotalRecap);



            document.getElementById("downloadExcel_totalrecap").addEventListener("click", function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        alert("Please select both start and end dates before downloading.");
        return;
    }

    const downloadUrl = `http://192.168.1.94:5000/download-totalrecap-excel?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000012`;
    window.location.href = downloadUrl;  // Triggers file download
});


let currentPage = 1;
const rowsPerPage = 10;
let fullData = [];


            // Debounce function to limit requests
            function debounce(func, delay) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func(...args), delay);
                };
            }
   
            // Fetch data when filters are applied
            async function fetchFournisseurRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

                if (!startDate || !endDate) return;

                const url = new URL("http://192.168.1.94:5000/fetchFournisseurData");
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000012"); 
                if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
                if (product) url.searchParams.append("product", product);
                if (client) url.searchParams.append("client", client);
                if (operateur) url.searchParams.append("operateur", operateur);
                if (bccb) url.searchParams.append("bccb", bccb);
                if (zone) url.searchParams.append("zone", zone);

                try {
                    showLoader();
                    const response = await fetch(url);
                    if (!response.ok) throw new Error("Network response was not ok");

                    const data = await response.json();
                    console.log("Fetched Data:", data);  // Debugging line to check if response contains data
                    updateFournisseurTable(data);
                    hideLoader();
                } catch (error) {
                    console.error("Error fetching fournisseur data:", error);
                    document.getElementById('recap-frnsr-table').innerHTML =
                        `<tr><td colspan="5" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
                    hideLoader();
                }
            }


            // Show loader animation
            function showLoader() {
                document.getElementById("recap-frnsr-table").innerHTML = `
        <tr id="loading-row">
            <td colspan="5" class="text-center p-4">Loading...</td>
        </tr>
    `;
            }

            // Hide loader after fetching data
            function hideLoader() {
                const loaderRow = document.getElementById("loading-row");
                if (loaderRow) loaderRow.remove();
            }

            // Format number with thousand separators & two decimals
            function formatNumberf(value) {
                if (value === null || value === undefined || isNaN(value)) return "";
                return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }


            document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("recap-frnsr-table");
    const fournisseurInput = document.getElementById("recap_fournisseur");

    tableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedFournisseurs = [...document.querySelectorAll(".selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        fournisseurInput.value = selectedFournisseurs.join(", ");

        // Manually trigger the input event to simulate user search
        fournisseurInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchFournisseurRecap();
    });
});




   
function updateFournisseurTable(data) {
    const tableBody = document.getElementById("recap-frnsr-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Save data globally for pagination
    fullData = data;

    // Separate total row
    const totalRow = data.find(row => row.FOURNISSEUR === "Total");
    const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    currentPage = Math.min(currentPage, totalPages);

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = filteredData.slice(start, end);

    // Append total row first
    if (totalRow) {
        tableBody.innerHTML += `
            <tr class="bg-gray-200 font-bold">
                <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.MARGE)}%</td>
            </tr>
        `;
    }

    // Then add paginated rows
    pageData.forEach(row => {
        tableBody.innerHTML += `
            <tr class="dark:bg-gray-700">
                <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.MARGE)}%</td>
            </tr>
        `;
    });

    // Update pagination text
    document.getElementById("pageIndicator").textContent = `Page ${currentPage} of ${totalPages}`;
}


document.getElementById("firstPage").addEventListener("click", () => {
    currentPage = 1;
    updateFournisseurTable(fullData);
});

document.getElementById("prevPage").addEventListener("click", () => {
    if (currentPage > 1) currentPage--;
    updateFournisseurTable(fullData);
});

document.getElementById("nextPage").addEventListener("click", () => {
    const totalPages = Math.ceil((fullData.filter(r => r.FOURNISSEUR !== "Total").length) / rowsPerPage);
    if (currentPage < totalPages) currentPage++;
    updateFournisseurTable(fullData);
});

document.getElementById("lastPage").addEventListener("click", () => {
    currentPage = Math.ceil((fullData.filter(r => r.FOURNISSEUR !== "Total").length) / rowsPerPage);
    updateFournisseurTable(fullData);
});


document.getElementById("download-fournisseur").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL("http://192.168.1.94:5000/download-fournisseur-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

            // Attach event listeners
            // Attach event listeners for all filters
            document.getElementById("start-date").addEventListener("change", fetchFournisseurRecap);
            document.getElementById("end-date").addEventListener("change", fetchFournisseurRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchFournisseurRecap, 500));

            let currentProductPage = 1;
const productRowsPerPage = 10;
let fullProductData = [];

document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-prdct-table");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-prdct-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        document.getElementById("recap_product").value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        document.getElementById("recap_product").dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchProductRecap();
    });
});

// Fetch data for product table
async function fetchProductRecap() {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchProductData");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012"); 
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received Data:", data);  // 🚀 Debugging line
        updateProductTable(data);
    } catch (error) {
        console.error("Error fetching product data:", error);
    }
}

// Update product table with pagination
function updateProductTable(data) {
    const tableBody = document.getElementById("recap-prdct-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Save data globally for pagination
    fullProductData = data;

    // Separate the "Total" row
    const totalRow = data.find(row => row.PRODUIT === "Total");
    const filteredData = data.filter(row => row.PRODUIT !== "Total");

    const totalPages = Math.ceil(filteredData.length / productRowsPerPage);
    currentProductPage = Math.min(currentProductPage, totalPages);

    const start = (currentProductPage - 1) * productRowsPerPage;
    const end = start + productRowsPerPage;
    const pageData = filteredData.slice(start, end);

    const fragment = document.createDocumentFragment();

    // Add total row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Add paginated rows
    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently

    // Update pagination text
    document.getElementById("productPageIndicator").textContent = `Page ${currentProductPage} of ${totalPages}`;
}

// Pagination controls
document.getElementById("firstProductPage").addEventListener("click", () => {
    currentProductPage = 1;
    updateProductTable(fullProductData);
});

document.getElementById("prevProductPage").addEventListener("click", () => {
    if (currentProductPage > 1) currentProductPage--;
    updateProductTable(fullProductData);
});

document.getElementById("nextProductPage").addEventListener("click", () => {
    const totalPages = Math.ceil(fullProductData.filter(r => r.PRODUIT !== "Total").length / productRowsPerPage);
    if (currentProductPage < totalPages) currentProductPage++;
    updateProductTable(fullProductData);
});

document.getElementById("lastProductPage").addEventListener("click", () => {
    currentProductPage = Math.ceil(fullProductData.filter(r => r.PRODUIT !== "Total").length / productRowsPerPage);
    updateProductTable(fullProductData);
});

// Format number for product table with thousand separators & two decimals
function formatNumberp(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}



document.getElementById("download-product-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();
    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL("http://192.168.1.94:5000/download-product-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

            // Format numbers with commas (thousands separator)
            function formatNumber(value) {
                return new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
            }

            // Attach event listeners to all search fields
            document.getElementById("start-date").addEventListener("change", fetchProductRecap);
            document.getElementById("end-date").addEventListener("change", fetchProductRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchProductRecap, 500));





            function debounce(func, delay) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func(...args), delay);
                };
            }

            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-zone-table");
    const productInput = document.getElementById("recap_zone");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-zone-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        updateZoneTable();
    });
});

// Pagination state for zone
let currentZonePage = 1;
let totalZonePages = 1;
const itemsPerZonePage = 10; // Number of items to display per page

// Fetch data when filters are applied for zone recap
async function fetchZoneRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchZoneRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012");
    url.searchParams.append("page", page);  // Add the page parameter for pagination
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received Data:", data); // Debugging log
        updateZoneTable(data);
        return data; // Ensure function returns data
    } catch (error) {
        console.error("Error fetching zone recap data:", error);
    }
}

// Update table with fetched zone data
function updateZoneTable(data) {
    const tableBody = document.getElementById("recap-zone-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    // Find and extract the "Total" row
    const totalRow = data.find(row => row.ZONE === "Total");
    const filteredData = data.filter(row => row.ZONE !== "Total");

    // Create and append the "Total" row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.ZONE}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Append remaining rows (apply pagination by slicing the data)
    const paginatedData = filteredData.slice((currentZonePage - 1) * itemsPerZonePage, currentZonePage * itemsPerZonePage);
    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.ZONE || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently

    // Update pagination
    updatePagination(filteredData.length);
}

// Update pagination controls for zone
function updatePagination(totalItems) {
    totalZonePages = Math.ceil(totalItems / itemsPerZonePage);

    const zonePageIndicator = document.getElementById("zonePageIndicator");
    zonePageIndicator.textContent = `Page ${currentZonePage} of ${totalZonePages}`;

    document.getElementById("firstzonePage").disabled = currentZonePage === 1;
    document.getElementById("prevzonePage").disabled = currentZonePage === 1;
    document.getElementById("nextzonePage").disabled = currentZonePage === totalZonePages;
    document.getElementById("lastzonePage").disabled = currentZonePage === totalZonePages;
}

// Handle pagination button clicks for zone
document.getElementById("firstzonePage").addEventListener("click", () => changeZonePage(1));
document.getElementById("prevzonePage").addEventListener("click", () => changeZonePage(currentZonePage - 1));
document.getElementById("nextzonePage").addEventListener("click", () => changeZonePage(currentZonePage + 1));
document.getElementById("lastzonePage").addEventListener("click", () => changeZonePage(totalZonePages));

// Change page for zone
function changeZonePage(page) {
    if (page < 1 || page > totalZonePages) return;
    currentZonePage = page;
    fetchZoneRecap(currentZonePage); // Fetch data for the new page
}

// Format numbers with commas (thousands separator)
function formatNumber(value) {
    return new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
}


            // Attach event listeners to all search fields
            document.getElementById("start-date").addEventListener("change", fetchZoneRecap);
            document.getElementById("end-date").addEventListener("change", fetchZoneRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchZoneRecap, 500));

// Download Zone Recap as Excel
document.getElementById("download-zone-excel").addEventListener("click", function () {
    downloadExcel("download-zone-excel");
});

// Download Client Recap as Excel
document.getElementById("download-client-excel").addEventListener("click", function () {
    downloadExcel("download-client-excel");
});
// Download Operator Recap as Excel
document.getElementById("download-Opérateur-excel").addEventListener("click", function () {
    downloadExcel("download-operator-excel");
});

// Download BCCB Recap as Excel
document.getElementById("download-BCCB-excel").addEventListener("click", function () {
    downloadExcel("download-bccb-excel");
});
document.getElementById("download-bccb-product-excel").addEventListener("click", function () {
    downloadExcel("download-bccb-product-excel");
});
function downloadExcel(endpoint) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL(`http://192.168.1.94:5000/${endpoint}`);
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    window.location.href = url;
}


let currentClientPage = 1;
let totalClientPages = 1;
const itemsPerClientPage = 10; // Number of items to display per page


async function fetchClientRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchClientRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012");
    url.searchParams.append("page", page);  // Add the page parameter for pagination
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received Client Recap Data:", data); // Debugging log
        updateClientTable(data);
        return data; // Ensure function returns data
    } catch (error) {
        console.error("Error fetching client recap data:", error);
    }
}


            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-client-table");
    const productInput = document.getElementById("recap_client");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-client-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchClientRecap();
    });
});


function updateClientTable(data) {
    const tableBody = document.getElementById("recap-client-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    // Find and extract the "Total" row
    const totalRow = data.find(row => row.CLIENT === "Total");
    const filteredData = data.filter(row => row.CLIENT !== "Total");

    // Create and append the "Total" row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.CLIENT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Append remaining rows (apply pagination by slicing the data)
    const paginatedData = filteredData.slice((currentClientPage - 1) * itemsPerClientPage, currentClientPage * itemsPerClientPage);
    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.CLIENT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently

    // Update pagination
    updateClientPagination(filteredData.length);
}
function updateClientPagination(totalItems) {
    totalClientPages = Math.ceil(totalItems / itemsPerClientPage);

    const clientPageIndicator = document.getElementById("clpageIndicator");
    clientPageIndicator.textContent = `Page ${currentClientPage} of ${totalClientPages}`;

    document.getElementById("clfirstPage").disabled = currentClientPage === 1;
    document.getElementById("clprevPage").disabled = currentClientPage === 1;
    document.getElementById("clnextPage").disabled = currentClientPage === totalClientPages;
    document.getElementById("cllastPage").disabled = currentClientPage === totalClientPages;
}
document.getElementById("clfirstPage").addEventListener("click", () => {
    currentClientPage = 1;
    fetchClientRecap(currentClientPage);
});

document.getElementById("clprevPage").addEventListener("click", () => {
    if (currentClientPage > 1) {
        currentClientPage--;
        fetchClientRecap(currentClientPage);
    }
});

document.getElementById("clnextPage").addEventListener("click", () => {
    if (currentClientPage < totalClientPages) {
        currentClientPage++;
        fetchClientRecap(currentClientPage);
    }
});

document.getElementById("cllastPage").addEventListener("click", () => {
    currentClientPage = totalClientPages;
    fetchClientRecap(currentClientPage);
});


            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchClientRecap);
            document.getElementById("end-date").addEventListener("change", fetchClientRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchClientRecap, 500));

// Button click triggers the fetching and chart creation
// Fetch the operator recap data normally, without waiting for the button click

let currentOperatorPage = 1;
let totalOperatorPages = 1;
const itemsPerOperatorPage = 10; // Number of items per page for operator recap




async function fetchOperatorRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchOperatorRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012");
    url.searchParams.append("page", page);  // Add page parameter for pagination
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received Operator Recap Data:", data); // Debugging log
        updateOperatorTable(data);  // Update table with fetched data
        return data; // Return fetched data
    } catch (error) {
        console.error("Error fetching operator recap data:", error);
    }
}




document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-operator-table");
    const productInput = document.getElementById("recap_operateur");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-operator-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchOperatorRecap();
    });
});
// Button click triggers the chart creation (after data is fetched)

// Update table with fetched data
function updateOperatorTable(data) {
    const tableBody = document.getElementById("recap-operator-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    // Find and extract the "Total" row
    const totalRow = data.find(row => row.OPERATEUR === "Total");
    const filteredData = data.filter(row => row.OPERATEUR !== "Total");

    // Create and append the "Total" row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.OPERATEUR}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Apply pagination by slicing the data
    const paginatedData = filteredData.slice((currentOperatorPage - 1) * itemsPerOperatorPage, currentOperatorPage * itemsPerOperatorPage);
    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.OPERATEUR || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently

    // Update pagination
    updatePaginationop(filteredData.length);
}
function updatePaginationop(totalItems) {
    totalOperatorPages = Math.ceil(totalItems / itemsPerOperatorPage);

    const pageIndicator = document.getElementById("oppageIndicator");
    pageIndicator.textContent = `Page ${currentOperatorPage} of ${totalOperatorPages}`;

    document.getElementById("opfirstPage").disabled = currentOperatorPage === 1;
    document.getElementById("opprevPage").disabled = currentOperatorPage === 1;
    document.getElementById("opnextPage").disabled = currentOperatorPage === totalOperatorPages;
    document.getElementById("oplastPage").disabled = currentOperatorPage === totalOperatorPages;
}

document.getElementById("opfirstPage").addEventListener("click", () => changeOperatorPage(1));
document.getElementById("opprevPage").addEventListener("click", () => changeOperatorPage(currentOperatorPage - 1));
document.getElementById("opnextPage").addEventListener("click", () => changeOperatorPage(currentOperatorPage + 1));
document.getElementById("oplastPage").addEventListener("click", () => changeOperatorPage(totalOperatorPages));

function changeOperatorPage(page) {
    if (page < 1 || page > totalOperatorPages) return;
    currentOperatorPage = page;
    fetchOperatorRecap(currentOperatorPage); // Fetch data for the new page
}


// Update chart with fetched data (only when clicking the button)
let allcharts = null; // Global chart variable

// Function to fetch and update the Operator chart
document.getElementById("generate-chart-operateur").addEventListener("click", async function () {
    const data = await fetchOperatorRecap();
    console.log("Fetched operateur Data chart without total:", data); // Debugging log

    if (data && data.length > 0) {
        // Filter out the "Total" row before passing the data to the chart
        const dataForChart = data.filter(row => row.OPERATEUR !== "Total");
        
        // Update the chart with the filtered data
        updateChart(dataForChart, "OPERATEUR");
    } else {
        console.warn("No data received for ZONE.");
    }
});

// Function to fetch and update the Zone chart
document.getElementById("generate-chart-zone").addEventListener("click", async function () {
    const data = await fetchZoneRecap();
    console.log("Fetched Zone Data chart without total:", data); // Debugging log

    if (data && data.length > 0) {
        // Filter out the "Total" row before passing the data to the chart
        const dataForChart = data.filter(row => row.ZONE !== "Total");
        
        // Update the chart with the filtered data
        updateChart(dataForChart, "ZONE");
    } else {
        console.warn("No data received for ZONE.");
    }
});



// Generic function to update the chart based on dataset type (Operator or Zone)
function updateChart(data, type) {
    if (!data || data.length === 0) {
        console.warn(`No data available for the ${type} chart.`);
        return;
    }

    // Extract "Total" row if available
    const totalRow = data.find(row => row[type] === "Total");
    const filteredData = data.filter(row => row[type] !== "Total");

    // Prepare labels and values
    const labels = filteredData.map(row => row[type]);
    const totalValues = filteredData.map(row => row.TOTAL);
    const qtyValues = filteredData.map(row => row.QTY);
    const margeValues = filteredData.map(row => row.MARGE * 100);

    // Include the "Total" row in the chart
    if (totalRow) {
        labels.unshift(totalRow[type]);
        totalValues.unshift(totalRow.TOTAL);
        qtyValues.unshift(totalRow.QTY);
        margeValues.unshift(totalRow.MARGE * 100);
    }

    console.log(`Chart Labels for ${type}:`, labels);
    console.log("Total Values:", totalValues);
    console.log("Qty Values:", qtyValues);
    console.log("Marge Values:", margeValues);

    const canvas = document.getElementById("allcharts");
    if (!canvas) {
        console.error("Canvas element not found!");
        return;
    }

    const ctx = canvas.getContext("2d");

    // Destroy previous chart before creating a new one
    if (allcharts instanceof Chart) {
        console.log("Destroying old chart...");
        allcharts.destroy();
    }

    // Render the new chart
    setTimeout(() => {
        allcharts = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Total",
                        data: totalValues,
                        backgroundColor: "rgba(54, 162, 235, 0.6)",
                    },
                    {
                        label: "QTy",
                        data: qtyValues,
                        backgroundColor: "rgba(255, 99, 132, 0.6)",
                    },
                    {
                        label: "Marge (%)",
                        data: margeValues,
                        backgroundColor: "rgba(75, 192, 192, 0.6)",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });

        console.log(`${type} chart successfully created!`);
    }, 100);
}

// Helper function to format numbers
function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchOperatorRecap);
            document.getElementById("end-date").addEventListener("change", fetchOperatorRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchOperatorRecap, 500));



let currentBccbPage = 1;
let totalBccbPages = 1;
const itemsPerBccbPage = 10; // Adjust this to the number of items per page

        
  // Fetch data for Recap by BCCB
  async function fetchBccbRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchBCCBRecapfact"); 
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("page", page);  // Add page parameter for pagination
    
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received BCCB Recap Data:", data); // Debugging log
        updateBccbTable(data);
        return data; // Return data for pagination
    } catch (error) {
        console.error("Error fetching BCCB recap data:", error);
    }
}
function formatNumberb(value) {
    return parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateString) {
    if (!dateString) return ''; // Return an empty string if no date provided

    const date = new Date(dateString);
    
    // Format the date as 'Wed, 26 Mar 2025'
    const options = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-GB', options); // 'en-GB' for British date format
}
// Debounce function to limit API calls on input change
function debounce(fn, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}


 // Update table with fetched data
 function updateBccbTable(data) {
    const tableBody = document.getElementById("recap-bccb-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();
    const totalRow = data.find(row => row.DOCUMENTNO === "Total");
    const filteredData = data.filter(row => row.DOCUMENTNO !== "Total");

    const paginatedData = filteredData.slice((currentBccbPage - 1) * itemsPerBccbPage, currentBccbPage * itemsPerBccbPage);

    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.DOCUMENTNO || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.DATEORDERED)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberb(row.GRANDTOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.MARGE !== null ? row.MARGE.toFixed(2) + '%' : "N/A"}</td>
        `;
        fragment.appendChild(tr);
    });

    // Append total row at the bottom
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600 text-right" colspan="2">Total:</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.GRANDTOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.MARGE !== null ? totalRow.MARGE.toFixed(2) + '%' : "N/A"}</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Append everything to the table
    tableBody.appendChild(fragment);

    // Update pagination
    updatePaginationbccb(filteredData.length);
}
function updatePaginationbccb(totalItems) {
    totalBccbPages = Math.ceil(totalItems / itemsPerBccbPage);

    const pageIndicator = document.getElementById("bpageIndicator");
    pageIndicator.textContent = `Page ${currentBccbPage} of ${totalBccbPages}`;

    document.getElementById("bfirstPage").disabled = currentBccbPage === 1;
    document.getElementById("bprevPage").disabled = currentBccbPage === 1;
    document.getElementById("bnextPage").disabled = currentBccbPage === totalBccbPages;
    document.getElementById("blastPage").disabled = currentBccbPage === totalBccbPages;
}


document.getElementById("bfirstPage").addEventListener("click", () => changeBccbPage(1));
document.getElementById("bprevPage").addEventListener("click", () => changeBccbPage(currentBccbPage - 1));
document.getElementById("bnextPage").addEventListener("click", () => changeBccbPage(currentBccbPage + 1));
document.getElementById("blastPage").addEventListener("click", () => changeBccbPage(totalBccbPages));

function changeBccbPage(page) {
    if (page < 1 || page > totalBccbPages) return;
    currentBccbPage = page;
    fetchBccbRecap(currentBccbPage); // Fetch data for the new page
}



            

            document.getElementById("recap_bccbclient").addEventListener("input", debounce(() => {
    const bccbInput = document.getElementById("recap_bccbclient").value.trim();
    fetchBccbRecap();
    
    if (!bccbInput) {
        // Hide the product table if BCCB is cleared
        document.getElementById("bccb-product-container").style.display = "none";
    }
}, 500));


            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-bccb-table");
    const productInput = document.getElementById("recap_bccbclient");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get selected BCCB (assuming only one should be selected)
        let selectedBccb = row.cells[0].textContent.trim();

        // Update input field
        productInput.value = selectedBccb;

        // Manually trigger input event
        productInput.dispatchEvent(new Event("input"));

        // Fetch BCCB Recap
        fetchBccbRecap();

        // Fetch BCCB Product (Fix: Use selectedBccb)
        fetchBccbProduct(selectedBccb);
    });
});

          
            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchBccbRecap);
            document.getElementById("end-date").addEventListener("change", fetchBccbRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchBccbRecap, 500));

 async function fetchBccbProduct(bccb) {
    if (!bccb) return;

    const tableContainer = document.getElementById("bccb-product-container");
    tableContainer.style.display = "none"; // Hide table before fetching

    const url = new URL("http://192.168.1.94:5000/fetchBCCBProductfact");
    url.searchParams.append("bccb", bccb);
    url.searchParams.append("ad_org_id", "1000012"); 

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received BCCB Product Data:", data); // Debugging log

        updateBccbProductTable(data);

        // Show table only if data exists
        if (data.length > 0) {
            tableContainer.style.display = "block";
        }
    } catch (error) {
        console.error("Error fetching BCCB product data:", error);
    }
}


function updateBccbProductTable(data) {
    const tableBody = document.getElementById("recap-bccb-product-table");
    tableBody.innerHTML = ""; // Clear previous content

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No product data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    data.forEach(row => {
        // Convert REMISE to a whole number percentage, default to 0%
        const remiseFormatted = row.REMISE ? Math.round(row.REMISE * 100) + "%" : "0%";

        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTY || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${remiseFormatted}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.MARGE || "N/A"}</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment);
}

// List all the input IDs you want to apply this to
const recapInputs = [
    'recap_fournisseur',
    'recap_product',
    'recap_zone',
    'recap_client',
    'recap_operateur',
    'recap_bccbclient'
];

// Add event listener for each one
recapInputs.forEach(id => {
    const input = document.getElementById(id);
    if (input) {
        input.addEventListener('focus', function() {
            this.value = ''; // Clear
            const event = new Event('input', { bubbles: true }); // Trigger 'input' event
            this.dispatchEvent(event);
        });
    }
});


            // Dark Mode Toggle Functionality
            const themeToggle = document.getElementById('themeToggle');
            const htmlElement = document.documentElement;

            // Load Dark Mode Preference from Local Storage
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'true') {
                htmlElement.classList.add('dark');
                themeToggle.checked = true;
            }

            // Toggle Dark Mode on Click
            themeToggle.addEventListener('change', () => {
                htmlElement.classList.toggle('dark');
                const isDarkMode = htmlElement.classList.contains('dark');
                localStorage.setItem('darkMode', isDarkMode);
            });

        </script>

</body>

</html>