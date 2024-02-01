
<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Required meta tags always come first -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
    </head>
    <style>
        table {
            width:100%;
            font-weight:500;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        td{
            padding: 8px;
            font-size: 12px;
        }
        .border_1{
            border: none!important;
        }
    </style>
    <body>
        <div class="container">
            <div class="mt-5"></div>
            <div class="row">
                <div class="col-xl-4 col-lg-5 col-md-6 col-sm-7">
                    <!--<h5 style="margin-bottom: 0px;">Billboardsindia.com</h5>-->
                    <h5 style="margin-bottom: 0px;">advertisingmarketplace.com</h5>
                    <p class="text-muted" style="margin-top: 0px;font-size:12px;"><i>Advertising has never been this easy</i></p>
                    <img src="<?php echo base_path('html'); ?>/assets/images/logo.jpg" alt="" style="margin-top: -50px;float: right;">
                </div>
                <div class="offset-xl-4 col-xl-4 col-lg-5 col-md-6 col-sm-7">

                </div>
            </div>
            <div class="row">
                <!--<div class="mt-5 col-xl-5 col-lg-5 col-md-6 col-sm-7">
                    <p style="font-size:12px;"><b>Plot No.1, Whitefields, Hitech City Road, Kondapur 
                            Hitech City, Hyderabad, <br>Telangana – 500084
                            Phone: 9550224488, 040-41239999<br>
                            Email: reach@billboardsindia.com<br>
                            GST No: 36AAHCB4122G120</b><p>
                </div>-->
		<div class="mt-5 col-xl-5 col-lg-5 col-md-6 col-sm-7">
                    <p style="font-size:12px;"><b>Richard McClemmy, <br>629 Terminal Way Suite 1,Costa Mesa,CA.92627
                            Phone: +(714)293-3883<br>
                            Email: reach@advertisingmarketplace.com<br>
                            GST No: </b><p>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    <h4 style="margin-bottom: 0px;">Campaign Name: <?php echo ucfirst($campaign->name); ?></h4>
                    <table>
                        <tr style="font-size: 12px;">
                           <th>Sr. No.</th>
                                <th>Product Type</th>
                                <th>Corridor</th>
                                <th>Trains</th>
                                <th>Slots</th>
                                <th>Start Date</th>
                                <th>Price</th>
                        </tr>
                        <tbody>
                            <?php
                            $i = 1;
                                foreach($packages as $package){ 
                                ?>
                                <tr align="center" style="font-size: 11px;">
                                         <td><?php echo $i; ?></td>
                                    <td><?php echo $package->format; ?></td>
                                    <td><?php echo $package->corridor_name; ?></td>
                                    <td><?php echo $package->selected_trains; ?></td>
                                    <td><?php echo $package->selected_slots; ?></td>
                                    <td><?php echo (new DateTime($package->start_date))->format('d-m-Y'); ?></td>
                                    <td><?php echo $package->price ?></td>
                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
                        </tbody>

                    </table>
                </div>
            </div>
           <div class="mt-5">
		<h4>Transporting Hyderabad to the Future</h4>
		<p><i class="fa fa-home"></i> 3 Lakh* Metro commuters per day, Metro train run time 16 hours per day</p>
		<p>Over 7 lakh passengers travel through the well-connected network of metro trains. These trains move in the heart of Hyderabad connecting all the areas within the city. There will be all types of commuters traveling through this metro trains.</p>
		<p>Digital signage inside the metro trains is the most engaging medium for entertainment and advertising. These 24’ Inch screens will entertain the audience in all sorts of ways from providing daily news, entertainment etc.</p>
		<p>Each brand can promote their advertisements to have the best attention of their target audience.</p>
	</div>
	<div class="mt-5">
		<h4>Product Details</h4>
		<ul>
			<li>Corridor 1 Miyapur to LB Nagar</li>
			<li>Distance covered - 29 Km</li>
			<li>No of Stations covered 27</li>
			<li>Ad duration - 30 sec</li>
			<li>12 screens per train</li>
			<li>Ad spots per day / per train 576</li>
			<li>Each train round trips 13 times  per day</li>
		</ul>
	</div>

            <!--<h4 class="text-center mt-5" style="margin-bottom: 0px;">Images of Product</h4>-->
            <?php
            /*$j = 0;
            foreach ($products as $product) {
                $prod_addr = isset($product->address) && !empty($product->address) ? $product->address : "";
                ?>
                <div style='width:680px; height:400px; margin:auto;background:#fff;'>
                    <?php
                    if (is_array($product->image)) {
                        foreach ($product->image as $k => $v) {
                            $img_src = base_path('html') . $v;
                            ?>
                            <div style="width:500px; height:350px; margin:auto;">
                                <img style="width:500px; height:350px; margin:auto;" src="<?php echo $img_src; ?>"/>
                            </div>
                            <?php
                        }
                    } else {
                        $img_src = base_path('html') . $product->image;
                        ?>
                        <div style="width:500px; height:350px; margin:auto;">
                            <img style="width:500px; height:350px; margin:auto;" src="<?php echo $img_src; ?>"/>
                        </div>
                        <?php
                    }
                    ?>


                    <p style="text-align:center; margin:auto; font-size:14px; font-weight:bold;"><?php echo ($j + 1) . ". " . $prod_addr ?> </p>
                </div>
                <?php
                $j++;
            }*/
            ?>
<h4 class="text-center mt-5" style="margin-bottom: 0px;">Images of Product</h4>
	<div class="row">
		<div class="">
			<img src="<?php echo base_path('html'); ?>/assets/images/metro_1.jpg" alt="" style="padding:40px;">
			<img src="<?php echo base_path('html'); ?>/assets/images/metro_2.jpg" alt="" style="padding:40px;">
		</div>
	</div>
            <p class="">The price provided in this Quotation has been tailor-made for your requirements.<br>
For further queries, Contact [ Richard McClemmy, CEO, Call: 9550224488 ].
</p>
            <!--<p class="">Thank you for your business! <br>
                <b>BBI Advertising Pvt Ltd</b></p>-->
	    <p class="">Thank you for your business! <br> 
		<b>AMP</b></p>

        </div>
    </body>
</html>

