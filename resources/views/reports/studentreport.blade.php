
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{$meta['student']}} - Progress Report</title>
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
            <h4 class="hlabel">Report Card</h4>
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
            <td>Student. </td>
            <td><b>{{ $meta['fullname'] }}</b></td>
            
            <td>Adm No. </td>
            <td><b>{{ $meta['admission'] }}</b></td>
            
            <td>Form. </td>
            <td><b>{{ $meta['flabel'] }}</b></td>

            <td>Stream. </td>
            <td><b>{{ $meta['slabel'] }}</b></td>
            
        </tr>
        <tr>
            <td>KCPE. </td>
            <td><b>{{ $meta['kcpe'] }}</b></td>

            <td>Co-curricular. </td>
            <td><b>{{ $meta['sport'] }}</b></td>

            <td>Class teacher. </td>
            <td><b>{{ $meta['cteacher'] }}</b></td>
            
            <td>Parent. </td>
            <td><b>{{ $meta['parentname'] }}</b></td>
        </tr>
    </table>

    <h4 class="text-4 mt-2"><b>{{$meta['termname']}} Performance</b></h4>
    <!-- SPlit parent -->
    <table id="content">
      <tr id="content-tr">
      <td id="content-td" style="width: 62%;">
        <!-- ==========LEFT -->
        <table class="table" id="customers" width="800px">
          <thead class="dark-head">
            <tr>
              <th style="text-align:center!important;" width="180px">Subjects</th>
              @if( count($headers) )
                @foreach( $headers as $head )
                <th style="text-align:center!important;" width="130px">{{ $head }}</th>
                @endforeach
              @endif
            </tr>
          </thead>
          <tbody>
            @if(count($marks))
              @foreach( $marks as $value )
                @php($string = explode('~', $value) )
                @php($str_a  = explode('__', $string[1]))
                @php($str_b  = explode('__', $string[2]))
                @php($str_c  = explode('__', $string[3]))
                  <tr>
                    <td style="text-align:left;">{{ $string[0] }}</td>
                    
                    @if(intval($str_a[0]) != 0)
                      @if(isset($str_a[1]))
                          <td>{{ $str_a[0] . " " . $str_a[1] }}</td>
                      @else
                        <td>{{ $string[1] }}</td>
                      @endif
                    @else
                      <td></td>
                    @endif

                    @if(intval($str_b[0]) != 0 )
                      @if(isset($str_b[1]))
                          <td>{{ $str_b[0] . " " . $str_b[1] }}</td>
                      @else
                        <td>{{ $string[2] }}</td>
                      @endif
                    @else
                      <td></td>
                    @endif

                    @if( intval($str_c[0]) != 0 )
                      @if(isset($str_c[1]))
                          <td>{{ $str_c[0] . " " . $str_c[1] }}</td>
                      @else
                        <td>{{ $string[3] }}</td>
                      @endif
                    @else
                      <td></td>
                    @endif
                  </tr>
              @endforeach
            @endif

            @if(count($avr))
              <tr>
                <td style="text-align:left;"><b>Average</b></td>
                @foreach( $avr as $average)
                  @php($str = explode('__', $average))
                  @if(intval($str[0]) != 0)
                    <td><b>{{ $str[0] . " " . $str[1] . " "}} <br> {{ $str[2] }}</b></td>
                  @endif
                @endforeach;
              </tr>
            @endif
          </tbody>
        </table>
        <!-- -======End -->
      </td>
      <td id="content-td" style="width: 38%; text-align:right;">
        <img src="{{ storage_path($chart) }}" width="350px" alt="trend"/><br>
      </td>
      </tr>
    </table>

    <!-- Cooments meta section -->
    <table class="table" id="customers" width="800px">
      <tbody>
        <tr>
            <td style="text-align:left;"><b>Last Term Performance.</b></td>
            <td><b>{{$last_perf}}</b></td>
            <td><b>Performance Deviation</b></td>
            <td><b>{{ $deviation }}</b></td>
        </tr>
        <tr>
            <td style="text-align:left;"><b>Fee balance.</b></td>
            <td><b>Ksh.{{$fees}}</b></td>
            <td><b>Next term fees.</b></td>
            <td><b>Ksh. _________ <br> Totals Ksh. _____________</b></td>
        </tr>
        <tr>
            <td style="text-align:left;"><b>Principal comments.</b></td>
            <td colspan="3"><b>_________________________________________________________________________</b></td>
        </tr>
        <tr>
            <td style="text-align:left;"><b>Class teacher comments.</b></td>
            <td colspan="3"><b>__________________________________________________________________________</b></td>
        </tr>
        <tr>
            <td style="text-align:left;"><b>Parent comments.</b></td>
            <td colspan="3"><b>__________________________________________________________________________</b></td>
        </tr>
        <tr>
            <td colspan="4">The school closes on <b>_____________</b>. The next TERM will start on <b>_______________________</b> and all students must report on the said date not later than 4.00PM.</td>
        </tr>
      </tbody>
    </table>
    <h4 class="text-4 mt-2"><b>Grading Scale</b></h4>
    <table class="table" id="customers" width="800px">
        <tr>
            <td>Grade</td>
            @foreach( $scale as $scl )
                <td>{{ $scl->grade }}</td>
            @endforeach
        </tr>
        <tr>
            <td>Score</td>
            @foreach( $scale as $gscl )
                <td>{{ $gscl->min_mark }} - {{ $gscl->max_mark }}</td>
            @endforeach
        </tr>
    </table>
    <hr>
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