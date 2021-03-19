<?php

namespace App\Http\Controllers;

use App\Services\SearchEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(SearchEngine $search_engine)
    {
      list($bikes, $rental_places, $bike_images) = $search_engine->performMapSearch([]);

      $view_data['bikes'] = $bikes;
      $view_data['rental_places'] = $rental_places;
      $view_data['bike_images'] = $bike_images;
      $view_data['search_mode'] = false;

      $form_data = [
        'location' => 'Wien',
        'date' => now()->format('Y-m-d'),
        'duration' => 2,
      ];

      $view_data['form_data'] = $form_data;
      $view_data['embed'] = false;
      $view_data['form_action'] = route('search.index');

      return view('search.index',$view_data);
    }


    /**
     * select app locale
     *
     * @param Request $request
     */
    public function selectLocale(Request $request)
    {
      $locale = $request->query('locale');
      $public_locales = explode(',', config('app.public_locales'));

      // only allow supported locales
      if (in_array($locale, $public_locales)) {
        session(['locale' => $locale]);
      }

      return redirect()->back();
    }

    /**
     * Create a cookie that indicates the user has seen the calendar usage hint.
     *
     * @param Request $request
     */
    public function calendarUsageOk(Request $request)
    {
      Cookie::queue('calendar_usage_ok',true,5256000);

      return response()->json("ok");
    }

    /**
     * Display info/faq.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function info()
    {
      $fields = [
        'what' => 0,
        'advantages' => 7,
        'capabilities' => 14,
        'howto' => 0,
        'requirements' => 10,
        'contribute' => 2,
        'station_why' => 7,
        'station_howto' => 0
      ];


      $view_data = [
        'fields' => $fields
      ];

      return view('home.info', $view_data);
    }

    /**
     * Display impressum.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function impressum()
    {
      return view('home.impressum');
    }

    /**
     * Display data protection information.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function dataProtection()
    {
      $fields = ['contact','data_storage','identity_confirmation','cookies','newsletter','personal_rights'];
      return view('home.data_protection', ['fields' => $fields]);
    }
}
