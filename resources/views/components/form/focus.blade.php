@php
if($errors->keys()){
$focus_field = $errors->keys()[0];
if(isset($mode)){
	if($mode === 'line'){
		$focus_field = str_replace('.', '_', $focus_field);
	}elseif($mode === 'array'){
		if(strpos($focus_field, '.') !== false){
			$exploded = explode('.', $focus_field);
			$focus_field = array_shift($exploded);
			foreach($exploded as $exp){
				$focus_field .= "[{$exp}]";
			}
		}
	}
}
@endphp
@push('scripts')
<script type="text/javascript">
$(function(){
	let focus_elem = $('label[for="{{$focus_field}}"]');
	if(!focus_elem.length){
		focus_elem = $('[id="{{$focus_field}}"]');
	}
	focus_elem.focus();
	focus_elem.parent()[0].scrollIntoView();
});
</script>
@endpush
@php
}
@endphp
