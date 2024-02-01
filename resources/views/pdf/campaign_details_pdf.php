<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Bootstrap CSS -->
    
  </head>
  <style>
  .container{

	font-family: sans-serif;
  }
   .for_scroll{
    height: 393px;
    overflow: auto;
 }
	table {
    		width:100%;
			font-weight:500;
			font-size:11px;
			border-collapse: collapse;
		}
		table, th {
    		border: 1px solid black;
			padding: 0px;
			text-align: center;
		}
		table, td {
    		border: 1px solid black;
			padding: 5px;
		}
		td{
		    padding: 5px;
			text-align: center;
		}
		.border_1{
			border: none!important;
		}
		.pay_now {
		  background-color: #BC2535;
		  border: none;
		  color: white;
		  padding: 10px 32px;
		  text-align: center;
		  text-decoration: none;
		  display: inline-block;
		  font-size: 12px;
		  margin: 4px 2px;
		  cursor: pointer;
		  font-weight:500;
		}
        .pdf-alignment p{
        	font-size: 12px;
        	margin-top: 0px;
        	margin-bottom: 0px;
        }
        .page-break {
		    page-break-before: always;
		}
		div.ex1 {
		  margin-top: 500px;
		}
  </style>
  <body>
    
 
    
<div class="container">
	<div class="row">
		<div class="" style="float: left;width:50%;">
			<img width="250px" src="<?php echo base_path('html'); ?>/assets/images/BBA.png" alt="" style="">
		</div>
		<div class="" style="float:right;line-height:15px;">
			<!--<h4 style="margin-bottom:0px;color:#990100;">BBA Advertising Pvt Ltd.</h4>-->
			<h4 style="margin-bottom:0px;color:#990100;">Advertising Marketplace</h4>
			<!--<p style="font-size:12px;">US West Coast 1110 112th Avenue NE <br>Suite 300C, Bellevue, WA. 98004<br>
				 Email: reach@billboardsamerica.com<br>Phone : 9550224488</p>-->
			<!--<p style="font-size:11px;">629 Terminal Way Suite 1, <br>Costa Mesa, CA 92627<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
				 
				<!--<p style="font-size:11px;">28832 Via Buena Vista, <br>San Juan Capistrano CA 92675<br>
				 Email: info@advertisingmarketplace.com<br>Phone : +(714)293-3883</p>-->
		</div>
	</div>
	<br />
	<br />
	
	<!--<hr style="width:100%;">
	
	<div class="row">
		<div class="" style="float: left;width:50%;line-height:17px;">
			<h4 style="margin-bottom:10px;color:#990100;">To,</h4>
			<p style="font-size:12px;">James, <br>Zomato Pvt Ltd,<br>
				james@email.com<br>9876543210<p>
		</div>
		<div class="" style="float:right;">
			<h4 style="margin-bottom:10px;color:#990100;letter-spacing:1px;">Invoice</h4>
			<p style="font-size:12px;"><b>Invoice No:</b> #0215 / <b>Date:</b> 13-06-2019</p>
		</div>
	</div>-->
	 
	<div class="row">
	<!--<div class="page-break">
		<div class="pdf-alignment">	-->
		<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
		<br/><br/><br/><br/>
			<h4 style="float:left;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;">Campaign Name: <?php echo ucfirst($campaign->name); ?></h4>
			<br/><br/><br />
			<table>
				<tr>
				<th>S No.</th>
					<!--<th style="text-align: left;padding:10px;">Tab ID</th>--> 
					<th style="text-align: left;padding:5px;">Product ID</th>
					<th>Type</th>
					<!--<th>Address</th>-->
					<th>DMA</th>
					<th>Size</th>
					<!--<th>Direction</th>--> 
					<th>Product Title</th>
					<th>Start Date</th>
					<th>End Date</th>
					<th>Unit Price<br>($)</th> 
					<th>Quantity</th>
					<th>Product Price<br>($)</th>
					<th>Tax amount<br>($)</th>
					<th>Net Total<br>($)</th>
					<?php if($campaign->offer_applied_res == 1){ ?>
					<th>New Price<br>($)</th>
					<?php } ?>
				</tr>
				<?php
                            $i = 1;
                            foreach ($products_arr as $product) {
                                ?>
                                <tr align="center" style="font-size: 10px;">
                                    <td><?php echo $i; ?></td>
                                    <td style="text-align: left;"><?php echo $product['siteNo']; ?></td>
                                    <td><?php echo $product['type']; ?></td>
                                    <!--<td><?php //echo $product['address']; ?></td>-->
                                    <!--<td><?php 
										//if(isset($product['address']) && !empty($product['address'])){
											//echo $product['address'];
										//}elseif(isset($product['addressone']) && !empty($product['addressone'])){
											//echo $product['addressone'];
										//}
									?></td>-->
                                    <td><?php echo $product['city']; ?></td>
                                    <td><?php echo $product['panelSize']; ?></td>
                                    <!--<td><?php //echo $product['direction']; ?></td>--> 
                                    <td><?php echo $product['title']; ?></td>
                                  
								  <?php  $booked_from_zone = date('m-d-Y', strtotime($product['booked_from']));
										$booked_to_zone = date('m-d-Y', strtotime($product['booked_to']));
									if(!is_null($product['area_time_zone_type'])){
										$start_time = new DateTime( $product['booked_from'] );
										$end_time = new DateTime( $product['booked_to'] );
										$laTimezone = new DateTimeZone($product['area_time_zone_type']);
										$start_time->setTimeZone( $laTimezone );
										$end_time->setTimeZone( $laTimezone );
										$booked_from_zone = $start_time->format( 'm-d-Y' );
										$booked_to_zone = $end_time->format( 'm-d-Y' );								 
									} ?>
								  
									<td><?php echo $booked_from_zone; ?></td>
									<!--<td><?php //echo date('m-d-Y',strtotime($product['from_date'])); ?></td>-->
									<td><?php echo $booked_to_zone; ?></td>
									<td><?php echo number_format($product['price'],2); ?></td>
									<td><?php if(isset($product['quantity']) && $product['quantity'] != 0 && $product['quantity'] != ''){ echo $product['quantity']; }else{ echo 'NA'; }?></td>
									<td><?php 
										if(isset($product['quantity']) && $product['quantity'] != 0 && $product['quantity'] != ''){
											echo number_format($product['price']*$product['quantity'],2);
										}else{
											echo number_format($product['price'],2);	
										}
									?></td>
									<td><?php if(isset($product['tax_percentage'])){ echo '('.$product['tax_percentage'].'%)'; }else{ echo'(0%)'; } ?><br><?php echo $product['tax_percentage_amount']; ?></td>
									<!--<td><?php //echo date('m-d-Y',strtotime($product['to_date'])); ?></td>-->
                                    <!--<td><?php // if(isset($product['owner_price']) && $product['owner_price']!=''){ $price =$product['owner_price'];}else{$price =$product['price'];} echo $price;?></td>-->
                                    <td>$<?php  if(isset($product['owner_price']) && $product['owner_price']!='')
									{ 
										if(isset($product['quantity']) && $product['quantity'] != 0 && $product['quantity'] != ''){
										$price =$product['owner_price']*$product['quantity'];
										}else{
										$price =$product['owner_price'];	
										}
									}else{
										if(isset($product['quantity']) && $product['quantity'] != 0 && $product['quantity'] != ''){
										$price =($product['price']+$product['tax_percentage_amount_shortlist'])*$product['quantity'];
										}else{
										$price =$product['price']+$product['tax_percentage_amount_shortlist'];	
										}
									}   
									echo number_format($price, 2);?></td>
									<?php if($campaign->offer_applied_res == 1){ ?>
													<td><?php echo number_format($product['offerprice'], 2); ?></td>
									<?php } ?>
                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
					</table>		
				 <table>
				<tr> 			 
					 <td class="border_1" style="color:#990100; border-right-style: hidden; padding-left:10px;"><b>Impressions Total -</b><b>  <?php echo round($campaign_impressionSum,0); ?></b></td>
					 <td class="border_1" style="color:#990100; border-right-style: hidden;"><b>CPM Total -</b><b>  $<?php echo sprintf('%.2f', $campaign_cpmval); ?></b></td>
					 <!--<td class="clr text-center" style="color:#990100;"><b><?php //echo '$'.$campaign_cpmval;?></b></td>-->
					 <td class="border_1" style="color:#990100; border-right-style: hidden;"><b>Total -</b>
					 <b> $<?php echo number_format($total_price, 2);?></b></td>	
				</tr>
			  
			</table>
			<br/>
			<!--<div style="margin-left:250px;margin-top:350px;" class="ex1">  
					<p style="color:#990100;font-size: 15px;margin-bottom: 0px;">Advertising Marketplace, LLC</p>
					<p style="font-size: 15px;margin-bottom: 0px; margin-top: 5px;">All Rights Reserved</p>
				</div>-->
			</div>
			 
				<!--</div>
		</div>-->
	</div>

	<!--<div style="margin-top:20px">
		<p style="font-size:12px;"><b>Total Number of Area Covered:</b> <?php echo $areas_covered; ?></p>
		<p style="font-size:12px;"><b>No. of Mediums Types Used:</b> <?php echo $format_types; ?>, No. of Mediums Covered: <?php echo $mediums_covered; ?></p>
		<p style="font-size:12px;"><b>Reach of Audiences:</b> <?php echo $audience_reach; ?>/week approx. (<?php echo $audience_reach * 4; ?>/Month approx.)</p>
		
	</div>-->
	
		<div class="row">
				<?php
				$temp = array_unique(array_column($products_arr, 'siteNo'));
				$unique_arr = array_intersect_key($products_arr, $temp);
				foreach($unique_arr as $key => $value){
					$j = 0;
					foreach ($products as $product) {
						if($value['siteNo'] == $product['siteNo']){
						$rateCard=$product['rateCard'];
						//$cpm=$product['cpm'];
						//$prod_addr = isset($product->address) && !empty($product->address) ? $product->address : ""; 
						$prod_addr = isset($product->addressone) && !empty($product->addressone) ? $product->addressone : "";

							//$page_break = 'page-break';$pb = $j+1;
						?>
						<div class="page-break">
						<!--<p style="text-align:center;"><?php //echo ($j + 1) . ". " .ucfirst($prod_addr); ?> </p>--> 
						<h4 style="margin-bottom:5px;color:#990100;margin-top:0px;">Product Details</h4>
						<div clas="" style="width: 100%;">
						

				<div class="" style="width: 50%; float: left;margin-top:0px;">				
				<div class="pdf-alignment">					
				<p class=""><b>AMP Product:</b> <?php echo $product['siteNo'] ;?></p>
				<p class=""><b>Type:</b> <?php echo $product['type'] ;?></p>
				<p class=""><b>Title:</b> <?php echo $product['title'] ;?></p>
				<p class=""><b>Description:</b> 
				<?php if(strlen($product['description']) > 45){
					echo substr($product['description'],0,45) . " ...";
				}
				else{
					echo $product['description']; 
				} ?></p>
				<p class=""><b>Address 1:</b> <?php echo $product['address'] ;?></p> 
				<p class=""><b>Address 2:</b> <?php echo $product['city'] ;?>,<?php echo $product['state'] ;?>,<?php echo $product['zipcode'] ;?></p>
				<p class=""><b>Country:</b> <?php echo $product['country_name'] ;?></p>
				<p class=""><b>Unit Quantity:</b> <?php echo $product['unitQty']; ?></p>
				<p class=""><b>Height:</b> <?php echo $product['height'] ;?> <b>Width:</b> <?php echo $product['width'] ;?></p>
				<!--<p class=""><?php //if($product['length']!=''){ ?><b>Length:</b> <?php //echo $product['length'] ; } ?></p>-->
				<p class=""><b>Panel Size:</b> <?php echo $product['panelSize'] ;?></p>
				<p class=""><b>Available 4-week Periods:</b> <?php echo sprintf('%.2f', $product['weekPeriod']) ;?></p>
				<p class=""><b>Rate Card (4 weeks):</b> $<?php echo number_format($rateCard, 2) ;?></p>
				<!--<p class=""><?php //if($product['fix']!=''){ echo '<b>Fixed Price:Yes</b>';  } ?></p>
				<p class=""><?php //if($product['fix']=''){ echo '<b>Fixed Price:No</b>';  } ?></p>-->
				<p class=""><b>Fixed/Variable:</b> <?php echo $product['fix']; ?></p>
				<!--<p class=""><b>Min Days:</b> <?php //echo number_format($rateCard, 2) ;?></p>-->  
				<p class=""><b>Installation Cost:</b> $<?php echo $product['installCost'] ;?></p>
				<p class=""><b>Production Cost:</b> $<?php echo $product['productioncost'] ;?></p>
				<p class=""><b>Product Direction:</b> <?php echo $product['imgdirection'] ;?></p>
				<!--<p class=""><?php //if($product['lighting']!=''){ ?><b>Lighting:</b> <?php //echo $product['lighting'];  } ?></p>-->
				<p class=""><?php if($product['lighting']!=''){ ?><b>Illumination:</b> <?php echo $product['lighting'];  } ?></p>
				<p class=""><?php if($product['direction']!=''){ ?><b>Facing:</b> <?php echo $product['direction'] ; } ?></p>
				<p class=""><b>Latitude:</b> <?php echo $product['lat'] ;?></p>
				<p class=""><b>Longitude:</b> <?php echo $product['lng'] ;?></p>
				<!--<p class=""><b>Restrictions:</b> <?php //echo $product['notes']; ?></p>-->
				<p class=""><b>Restrictions:</b> 
				<?php if(strlen($product['notes']) > 45){
					echo substr($product['notes'],0,45) . " ...";
				}
				else{
					echo $product['notes']; 
				} ?></p>
				<p class=""><b>Cancellation Policy:</b> 
				<?php if(strlen($product['cancellation_policy']) > 35){
					echo substr($product['cancellation_policy'],0,35) . " ...";
				}
				else{
					echo $product['cancellation_policy']; 
				} ?></p>
				<p class=""><b>Payment Terms:</b> 
				<?php if(strlen($product['cancellation_terms']) > 35){
					echo substr($product['cancellation_terms'],0,35) . " ...";
				}
				else{
					echo $product['cancellation_terms']; 
				} ?></p>
				<!--<p class=""><b>Cancellation Policy:</b> <?php //echo $product['cancellation_policy']; ?></p>-->
				<p class=""><b>Median HHI:</b> $<?php echo $product['mediahhi']; ?></p>
				<p class=""><b>Impression 18 - 49:</b> <?php echo $product['firstImpression']; ?></p>
				<p class=""><b>CPM Adults 18 - 49:</b> $<?php echo sprintf('%.2f', $product['firstcpm']); ?></p>
				<p class=""><b>Impression 18 - Plus:</b> <?php echo $product['secondImpression'] ;?></p>
				<p class=""><b>CPM Adults 18 - Plus:</b> $<?php echo sprintf('%.2f', $product['cpm']) ;?></p>
				<p class=""><b>Impression 25 - 54:</b> <?php echo $product['thirdImpression']; ?></p>
				<p class=""><b>CPM Adults 25 - 54:</b> $<?php echo sprintf('%.2f', $product['thirdcpm']); ?></p>
				<p class=""><b>Impression (HA25 - HA54):</b> <?php echo $product['forthImpression']; ?></p>
				<p class=""><b>CPM Hispanic 25 - 54:</b> $<?php echo sprintf('%.2f', $product['forthcpm']); ?></p>
				<p class=""><b>Audited:</b> <?php echo $product['audited']; ?></p>
				<p class=""><?php if($product['Comments']!=''){ ?><b>Comments:</b> <?php echo $product['Comments']; } ?></p>
				<p class=""><b>Seller Id:</b> <?php echo $product['sellerId']; ?></p>
				 
				<!--<p class=""><b>4-week Negotiated Net Cost:</b> $<?php //echo $product['negotiatedCost'] ;?></p>-->
				
				<!--<p class=""><b>Billing:</b><?php //echo $product['billingYes'] ;?></p>-->
				<p class=""><?php if($product['billingYes']!=''){ ?><b>AMP Bills:</b> <?php echo $product['billingYes'];  } ?></p>
				<p class=""><?php if($product['billingNo']!=''){ ?><b>AMP Bills:</b> <?php echo $product['billingNo'];  } ?></p>
				<p class=""><?php if($product['servicingNo']!=''){ ?><b>AMP Services:</b> <?php echo $product['servicingNo'];  } ?></p>
				<p class=""><?php if($product['servicingYes']!=''){ ?><b>AMP Services:</b> <?php echo $product['servicingYes'];  } ?></p>
				<p class=""><?php if($product['spotLength']!=''){ ?><b>Spot Length:</b> <?php echo $product['spotLength'];  } ?></p>
				<p class=""><?php if($product['fliplength']!=''){ ?><b>Flip Length:</b> <?php echo $product['fliplength'];  } ?></p>
				<p class=""><?php if($product['ageloopLength']!=''){ ?><b>Loop Length:</b> <?php echo $product['ageloopLength'];  } ?></p> 
				<p class=""><?php if($product['medium']!=''){ ?><b>Medium:</b> <?php echo $product['medium'];  } ?></p>
				<p class=""><?php if($product['product_newAge']!=''){ ?><b>Product:</b> <?php echo $product['product_newAge'];  } ?></p>

                <p class=""><?php if($product['file_type']!=''){ ?><b>File Type:</b> <?php echo $product['file_type'];  } ?></p>
                <p class=""><?php if($product['locationDesc']!=''){ ?><b>Location Description:</b> <?php echo $product['locationDesc'];  } ?></p>
				<p class=""><?php if($product['staticMotion']!=''){ ?><b>Static Motion:</b> <?php echo $product['staticMotion'];  } ?></p>
				<p class=""><?php if($product['sound']!=''){ ?><b>Sound:</b> <?php echo $product['sound'];  } ?></p>
				<p class=""><?php if($product['minimumdays']!=''){ ?><b>Minimum Days:</b> <?php echo $product['minimumdays'];  } ?></p>
				<p class=""><?php if($product['network']!=''){ ?><b>Network:</b> <?php echo $product['network'];  } ?></p>
				<p class=""><?php if($product['nationloc']!=''){ ?><b>National Local:</b> <?php echo $product['nationloc'];  } ?></p>
				<p class=""><?php if($product['daypart']!=''){ ?><b>Day Part:</b> <?php echo $product['daypart'];  } ?></p>
				<p class=""><?php if($product['genre']!=''){ ?><b>Genre:</b> <?php echo $product['genre'];  } ?></p>
				<p class=""><?php if($product['costperpoint']!=''){ ?><b>Cost Per Point:</b> <?php echo $product['costperpoint'];  } ?></p>
				<!--<p class=""><?php //if($product['length']!=''){ ?><b>Length:</b> <?php //echo $product['length'];  } ?></p>-->
				<p class=""><?php if($product['costperpoint']!=''){ ?><b>Cost Per Point:</b> <?php echo $product['costperpoint'];  } ?></p>
				<!--<p class=""><?php //if($product['length']!=''){ ?><b>Length:</b> <?php //echo $product['length'];  } ?></p>-->
				<p class=""><?php if($product['placement']!=''){ ?><b>Ad Type:</b> <?php echo $product['placement'];  } ?></p>
				
				
				</div>
				</div>

				<div class="" style="width: 50%; float: right;margin-top:0px;">
							<div>								<?php
                    if (is_array($product->image)) {
                        foreach ($product->image as $k => $v) {
                            $img_src = base_path('html') . $v;
                            ?>
								<p>
									<img style="width:400px;height:300px;" src="<?php echo $img_src; ?>">  
								</p>
                            <?php
                        }
                    } 
					//else {
                        //$img_src = base_path('html') . $product->image;
                        ?>
						<!--<p>
							 <img style="width:400px;height:400px;float:right;" src="<?php //echo $img_src; ?>"/>  
						</p>-->
                        <?php
                    //}
                    ?>
                   
					</div>
					<div>
						<?php $lat="";$lng=""; 
							if($product['lat']!=''){ 
								$lat=str_replace('°', '', $product['lat']);
							} 
							if($product['lng']!=''){ 
								$lng=str_replace('°', '', $product['lng']);
							} 
							if($lat!='' && $lng!=''){ 
								$is_lat = preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $lat);
								$is_lng = preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/', $lng);
								if($is_lat && $is_lng){ ?>
									<p>
										<img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $lat; ?>,<?php echo $lng; ?>&markers=color:red%7Clabel:C%7C<?php echo $lat; ?>,<?php echo $lng; ?>&zoom=12&size=400x300&key=<?php echo env('GOOGLE_MAP_KEY'); ?>"/>
									</p>
						<?php } } ?>
					</div>	
					 </div>								
</div>
				<br/> 
			 <div style="margin-top:860px;"> 
					<p style="color:#990100;font-size: 15px;margin-bottom: 0px;">Advertising Marketplace, LLC</p>
					<p style="font-size: 15px;margin-bottom: 0px; margin-top: 5px;">All Rights Reserved</p>
				</div>
</div>
				

				<?php
					}
					$j++;
				}
				}
				?>
				
		</div>
	<!--<p class="mt-3 text-center">The price provided in this Quotation has been tailor-made for your requirements.</p>--> 
	<!--<p class="">For further queries, Contact <b style="color:#990100;">[ Chanikya, CEO, Call: 9550224488 ]</b></p>-->
	
	<!--<p style="color:#990100;">BBI Advertising Pvt Ltd.</p>--> 

	
</div>
	<!--<p style="color:#990100;font-size: 15px;margin-bottom: 0px;">Advertising Marketplace, LLC</p>
	<p style="font-size: 15px;margin-bottom: 0px; margin-top: 5px;">All Rights Reserved</p>-->
</body>
</html>
