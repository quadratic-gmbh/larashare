@extends('layouts.app')
@section('content')
  <h1 class="text-primary">Impressum</h1>
  <p>
    <b>Impressum</b><br>
    Verein zur Förderung von Lastenrädern<br>
    Petersgasse 35<br>
    8010 Graz<br>
    <a href="mailto:team@das-lastenrad.at">team@das-lastenrad.at</a><br>
    <a href="https://www.das-lastenrad.at" target="_blank">www.das-lastenrad.at</a><br><br>
    ZVR-Zahl: 576525291<br>
    Steiermark Landespolizeidirektion LPD
  </p>
  <p>
    <b>Technische Umsetzung</b><br>
    <a href="https://www.quadratic.at/" target="_blank">quadratic GmbH</a>
  </p>  
  <p>
    <b>Partner</b><br>
    <a href="https://klimaentlaster.at">KlimaEntLaster</a><br>
    <a href="https://raum.tuwien.ac.at/forschungsbereiche/verkehrssystemplanung/DE">TU Wien - Institut für Raumplanung - Verkehrssystemplanung</a><br>
    <a href="https://radvokaten.at">die radvokaten</a><br>
    <a href="https://energy-changes.com">Energy Changes</a><br>
    <a href="https://factum.at">Factum - apptec Ventures</a><br>
    <a href="https://info.larashare.at/">LARA Share</a>
  </p>
  <p><a href="{{ asset('allgemeine_nutzungsbedingungen.pdf') }}" target="_blank">Nutzungsbedingungen</a></p>
  <p><a href="{{ route('data_protection') }}" target="_blank">Datenschutz</a></p>
@endsection
