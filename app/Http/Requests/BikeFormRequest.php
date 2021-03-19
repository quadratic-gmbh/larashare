<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use App\Rules\DecimalRule;

class BikeFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
      /*$route = Route::currentRouteName();
      if($route === 'bike.store'){
        return true;
      }else{
        return false;
      }*/
      return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      $rules =  [
          'name' => ['required', 'string', 'max:255'],
          'model' => ['required', 'string', 'max:255'],
          'wheels' => ['required', 'integer', 'between:2,4'],
          'children' => ['required', 'integer', 'between:0,4'],
          'electric' => ['required', 'boolean'],
          'box_type_id' => ['required', 'integer', 'exists:box_types,id'],
          'cargo_weight' => ['required', 'integer', 'between:0,200'],
          'cargo_length' => ['required', 'integer', 'between:0,300'],
          'cargo_width' => ['required', 'integer', 'between:0,150'],
          'misc_equipment' => ['nullable', 'string'],
          'description' => ['nullable', 'string'],
          'buffer_time_before' => ['nullable', 'integer', 'between:1,300'],
          'buffer_time_after' => ['nullable', 'integer', 'between:1,300'],
          'pricing_free' => ['required', 'boolean'],
          'pricing_donation' => ['required', 'boolean'],
          'pricing_value_hourly' => ['nullable', new DecimalRule],
          'pricing_value_daily' => ['nullable', new DecimalRule],
          'pricing_value_weekly' => ['nullable', new DecimalRule],
          'pricing_deposit' => ['nullable', new DecimalRule],
          'terms_of_use_file' => ['file', 'mimes:pdf'],
          'rental_place_counter' => ['required', 'integer', 'min:1'],
          'rental_place.*.name' => ['required', 'string', 'max:255'],
          'rental_place.*.street_name' => ['required', 'string', 'max:255'],
          'rental_place.*.house_number' => ['required', 'string', 'max:10'],
          'rental_place.*.postal_code' => ['required', 'string', 'max:10'],
          'rental_place.*.city' => ['required', 'string', 'max:255'],
          'rental_place.*.description' => ['nullable', 'string'],
          'rental_place.*.email_counter' => ['required', 'integer', 'min:1'],
          'rental_place.*.email.*.email' => ['required', 'email:rfc,dns', 'max:255'],
          'rental_place.*.email.*.notify_on_reservation' => ['required', 'boolean'],
        ];
      
      if(Route::currentRouteName() === 'bike.update'){
        $rules['delete_terms_of_use_file'] = ['nullable', 'boolean'];
        $rules['rental_place.*.id'] = ['nullable', 'integer', 'min:1'];
        $rules['rental_place.*.email.*.id'] = ['nullable', 'integer', 'min:1'];
      }
      
      return $rules;
    }
    
    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
      $validator->after(function ($validator) {
        $data = $validator->getData();
        
        if($data['pricing_free'] || $data['pricing_donation']){
          if(isset($data['pricing_value_hourly'])){
            $validator->errors()->add('pricing_value_hourly', __('bike.form.errors.pricing.not_with_free_or_donation'));
          }
          if(isset($data['pricing_value_daily'])){
            $validator->errors()->add('pricing_value_daily', __('bike.form.errors.pricing.not_with_free_or_donation'));
          }
          if(isset($data['pricing_value_weekly'])){
            $validator->errors()->add('pricing_value_weekly', __('bike.form.errors.pricing.not_with_free_or_donation'));
          }
        }
        
        if(!($data['pricing_free'] || $data['pricing_donation'] || isset($data['pricing_value_hourly']) || 
             isset($data['pricing_value_daily']) || isset($data['pricing_value_weekly']))){
          foreach(['pricing_free', 'pricing_donation', 'pricing_value_hourly', 'pricing_value_daily', 'pricing_value_weekly'] as $field){
            $validator->errors()->add($field, __('bike.form.errors.pricing.one_required'));
          }
        }
        
        if(!isset($data['rental_place'])){
          $validator->errors()->add('rental_place', __('bike.form.errors.rental_place.one_required'));
          return;
        }
        
        $i = 0;
        foreach($data['rental_place'] as $rp){
          $emails = [];
          $i++;
          $amount_emails = 0;
          
          if(!isset($rp['email'])){
            $validator->errors()->add('rental_place.' . $i . '.email', __('bike.form.errors.email.one_required'));
            return;
          }
          
          foreach($rp['email'] as $em){
            $emails[] = strval($em['email']);
            $amount_emails++;
          }
          
          $counted_vaues = array_count_values($emails);
          for($j = 1; $j <= $amount_emails; $j++){
            if($counted_vaues[$emails[$j-1]] > 1){
              $validator->errors()->add('rental_place.' . $i . '.email.' . $j . '.email', __('bike.form.errors.email.duplicates'));
            }
          }
        }
        
        if(isset($data['delete_terms_of_use_file']) && $data['delete_terms_of_use_file'] && isset($data['terms_of_use_file'])){
          foreach(['terms_of_use_file', 'delete_terms_of_use_file'] as $field){
            $validator->errors()->add($field, __('bike.form.errors.terms_of_use.not_both'));
          }
        }
      });
    }
}
