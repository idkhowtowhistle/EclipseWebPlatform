<?php
require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";
if(!SESSION) redirect("/");
pageBuilder::buildHeader();
?>

<div class="app py-4 container">
   <h3 class="mb-2 font-weight-normal">Transactions</h3>

   <div class="container">
        <div class="row">
            <div class="col-sm">
                <div class="m-auto" style="max-width: 60rem">
                   <div class="card bg-light shadow-sm">
                   <div class="card-body">
                    <table class="table" id="economy">
                        <thead>
                            <tr>
                             <th scope="col">ID</th>
                             <th scope="col" style="max-width: 12rem">Action</th>
                             <th scope="col">Member</th>
                             <th scope="col">Price</th>
                             <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                             <th scope="row">1</th>
                             <th><strong>Bought </strong><a href="#">Lol</a></th>
                             <th>chess</th>
                             <th style="color:red;">-5 <i class="far fa-money-bill-alt"></i></th>
                             <th>29/03/21</th>
                            </tr>
                            <tr>
                             <th scope="row">2</th>
                             <th><strong>Created </strong><a href="#">KRYPTON IRL</a></th>
                             <th>taskmanager</th>
                             <th style="color:red;">-5 <i class="far fa-money-bill-alt"></i></th>
                             <th>31/03/21</th>
                            </tr>
                            <tr>
                             <th scope="row">3</th>
                             <th><strong>Sold </strong><a href="#">KRYPTON IRL</a></th>
                             <th>Multako</th>
                             <th style="color:green;">+5 <i class="far fa-money-bill-alt"></i></th>
                             <th>1/04/21</th>
                            </tr>
                        </tbody>
                    </table>                  
                   </div>
                   <div class="card-footer">Total transactions: 3</div>
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
            $(document).ready(function () {
                //Pagination full
                $('#economy').DataTable({
                "pagingType": "simple_numbers",
	            "paging": true
                });
            });
             </script>
<?php pageBuilder::buildFooter(); ?>