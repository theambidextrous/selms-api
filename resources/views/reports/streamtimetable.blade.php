
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{$stream}} - Timetable</title>
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
  text-align:center;
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
</style>
</head>
<body>
<!-- Container -->
<!-- <div class="container-fluid invoice-container">  -->
  <!-- Header -->
  <header>
    <div class="row align-items-center">
    <table class="table" id="customers" width="800px">
      <tr class="td-borderless" style="background-color: #ffffff !important;">
        <td class="td-borderless" style="text-align:left!important;width:450px">
            {{$setup['school']}}<br>
            {{$setup['address']}}<br>
            {{$setup['city']}}, {{$setup['zip']}}<br>
            Website #: {{$setup['website']}}<br>
        </td>
        <!-- <td class="td-borderless" style="width:300px!important;"></td> -->
        <td class="td-borderless" style="text-align:right!important;width:350px">
            School Timetable<br>
            {{ $stream }}<br>
            C.Teacher #{{ $teacher }}<br>
            {{ date('m/d/Y') }}<br>
        </td>
      </tr>
      </table>      
    </div>
    <hr>
  </header>
  <!-- Main Content -->
    <!-- timetable ================== -->
    <h4 class="text-4 mt-2"><b>{{$stream}} Timetable</b></h4>
   <table class="table" id="customers" width="800px">
      <thead class="dark-head">
        <tr>
          <th width="60px">Days</th>
          <th>Lessons</th>
        </tr>
      </thead>
      <tbody>
        @if(count($timetable))
          @foreach( $timetable as $trp )
              <tr>
                <td>{{ substr($trp['day'], 0, 3) }}</td>
                <td style="text-align:left!important;">
                  <table>
                    <tr>
                      @if(count($trp['lessons']))
                        @foreach( $trp['lessons'] as $less )
                            <td width="120px" style="text-align:center!important;">
                                <span class="heavier"><b>{{ $less['time'] }}hrs</b></span><br></br>
                                <span class="bending">{{ $less['sublabel'] }}</span><br></br>
                                {{ $less['tlabel'] }}<br></br>
                                {{ $less['slabel'] }}
                            </td>
                        @endforeach
                      @endif
                    </tr>
                  </table>
                </td>
              </tr>
          @endforeach
        @endif
      </tbody>
    </table>
    <br>

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