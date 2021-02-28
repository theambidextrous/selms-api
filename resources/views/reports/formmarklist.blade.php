
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{$meta['form']}} - Mark List</title>
<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
  font-size:12px;
}
.heavier{
  font-weight: 800!important;
}
.bending{
  font-style: italic!important;
}
#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 5px;
  text-align:center!important;
}
.td-borderless{
  border: 0px solid #ddd!important;
  margin: 0px!important;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 4px;
  padding-bottom: 4px;
  text-align: left;
  background-color: #036a6b;
  color: white;
}
.d-hr{
    background-color: #036a6b;
    color: #036a6b;
    height:4px;
}
.p-label{
    text-align:center!important;
    font-size:23px!important;
    font-weight:800!important;
    color: #036a6b!important;
    max-width:150px!important;
}
.hlabel{
    text-align:right!important;
    float:right;
    font-size:30px!important;
    font-weight:800!important;
    padding-top:40px;
    background-color: #036a6b!important;
    color:#fff!important;
    height:80px;
}
</style>
</head>
<body>
<!-- Container -->
<!-- <div class="container-fluid invoice-container">  -->
  <!-- Header -->
  <header>
    <div class="row align-items-center">
    <table rules="none" class="table" id="customers" width="1200px">
      <tr class="td-borderless" style="background-color: #ffffff !important;border:none;">
        <td width="350px" class="td-borderless" style="width:150px!important;border-color:#fff;">
            <img src="{{ storage_path( 'app/cls/trt/content/' . $setup['logo'] ) }}" width="100px" alt="logo"/><br>
            <p class="p-label">{{ $setup['school'] }}</p>
        </td>
        <td width="150px" class="td-borderless" style="border-color:#fff;">
        </td>
        <td width="250px" class="td-borderless hlabel" >
            <h4 class="hlabel">{{$meta['form']}} Mark List</h4>
        </td>
      </tr>
      </table>      
    </div>
    <hr class="d-hr">
    <br>
  </header>
  <!-- Main Content -->
    <!-- student meta ================== -->   
    <table class="table" id="customers" width="800px">
        <tr>
            <td>Form. </td>
            <td><b>{{ $meta['form'] }}</b></td>
            <td>Assessment. </td>
            <td><b>{{ $meta['cat'] }}</b></td>
        </tr>
        <tr>
            <td>Enrollment. </td>
            <td><b>{{ $meta['count'] }}</b></td>
            
            <td>Term. </td>
            <td><b>{{ $meta['termname'] }}</b></td>
        </tr>
    </table>


    @if( count($marks) )
        @foreach( $marks as $_mark )
        <h4 class="text-4 mt-2"><b>{{$_mark['title']}}</b></h4>
        <table class="table" id="customers" width="800px">
            <thead class="dark-head">
                <tr>
                    <th>Admission</th>
                    <th>Full name</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                    <th>Summation</th>
                </tr>
            </thead>
            <tbody>
                @if( count($_mark['data']) )
                    @foreach( $_mark['data'] as $list )
                    <tr>
                        <td>{{ $list['admission'] }}</td>
                        <td>{{ $list['fullname'] }}</td>
                        <td>{{ $list['score'] }}</td>
                        <td>{{ $list['grade'] }}</td>
                        <td>{{ $list['remark'] }}</td>
                        <td>{{ $list['sum'] }}</td>
                    </tr>
                    @endforeach;
                @endif
                <tr>
                    <td colspan="4"><b>Mean Score</b></td>
                    <td colspan="2"><b>{{ $_mark['mean'] . " " . $_mark['meangrade']}}</b></td>
                </tr>
            </tbody>
        </table>
        @endforeach
    @endif
    
  <!-- Footer -->
  <footer class="text-center">
    <br>
    <hr>
    <p class="text-center">{{ $setup['school'] }} | {{$setup['address']}} | {{ $setup['county'] }} {{ $setup['zip'] }} <br><i><span style="color:#036a6b;">{{ Config::get('app.name') }}. Web: {{ Config::get('app.url')}}</span> </i></p>
  </footer>
<!-- </div> -->
<!-- Back to My Account Link -->
</body>
</html>