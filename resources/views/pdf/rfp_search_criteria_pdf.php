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
		
		
		
		
		
		.main_head h1{
			text-align: center;
			font-weight: 700;
		}
	.RFP_logo img {
		margin-left: 30%;
	}
	.rfp_data span {
		color: #000000;
	}
	.rfp_data span p {
		border : 1px solid #000000;
	}
  </style>
  
  <body>
<div class="container">
	<div class="row">	
		<div class="RFP_logo" style="">
			<img width="250px" src="<?php echo base_path('html'); ?>/assets/images/BBA.png" alt="" style="">
		</div>
		<div class="main_head" style="">
			<h1 style="">Request For Proposal</h1>
		</div>
	</div>
			<div class="row">
				<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
				
					<div class="rfp_data" style="float:left;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;"><b>AMP #:</b><span></span> </div><br/><br/>
					<div class="rfp_data" style="float:left;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;">CAMPAIGN:<span><?php echo isset($campaign_data['name']) ? $campaign_data['name'] : 'Not Available'; ?></span></div><br/><br/>
					
						<div class="rfp_data" style="float:left;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;">DEMO:<span> <?php if ($rfp_search_criteria) { echo $rfp_search_criteria['demo']; }?></span></div><br/><br/>
					<div class="rfp_data" style="float:left;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;">FORMATS: <span><?php if ($rfp_search_criteria) { echo implode(', ', $rfp_search_criteria->product_type); }?></span></div><br/><br/>
						<div class="rfp_data" style="float:left;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;">BUDGET: <span><?php if ($rfp_search_criteria) { echo $rfp_search_criteria['budget']; }?></span></div><br/><br/>
						<div class="rfp_data" style="float:left;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;">DEADLINE:<span style="font-weight:900;"> <?php if ($rfp_search_criteria) { echo date('m-d-Y',strtotime($rfp_search_criteria['due_date'])); }?></span></div><br/><br/>
						<div class="rfp_data" style="float:left;width:100%;color:#990100;letter-spacing:1px;font-size: 15px;">INSTRUCTIONS: <span><?php if ($rfp_search_criteria) {echo $rfp_search_criteria['instructions']; }?></span></div><br/>
				
					<br/><br/>
						<table>
								<tr> 
									<th style="text-align: left;padding:5px; background-color:#e6ff99">EST'D FLIGHT START</th>
									<th style="background-color:#e6ff99">FLIGHT END</th>
									<th style="background-color:#e6ff99">CITY</th>
									<th style="background-color:#e6ff99">STATE</th> 
									<th style="background-color:#e6ff99">FLIGHT DAYS</th>
								</tr>
								<?php
									//foreach ($rfp_search_criteria['dma_area'] as $key => $rfp_search_area) {
									foreach ($rfp_search_criteria['dma_area'] ?? [] as $key => $rfp_search_area) {
										$explode_dates = @explode('::',$rfp_search_criteria["dma_dates"][$key]);
										$start_date = strtotime($explode_dates[0]);
										$end_date = strtotime($explode_dates[1]);
										?>
										<tr align="center" style="font-size: 10px;">
											<td><?php echo date('l, F d, Y', strtotime($explode_dates[0])); ?></td>
											<td><?php echo date('l, F d, Y', strtotime($explode_dates[1])); ?></td>
											<td><?php echo $areas_data[$key]['city_name']; ?></td>
											<td><?php echo $areas_data[$key]['state_name']; ?></td> 
											<td><?php echo round(($end_date - $start_date)/60/60/24,2); ?></td> 
											
										</tr>
										<?php
									}
									?>
						</table>	
				</div>
			</div>
		</div>
	</body>
</html>
