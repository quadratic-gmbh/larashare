export function timeToMinutes(value) {
  let parts = value.split(':');
  let hours = Number.parseInt(parts[0]);
  let minutes = Number.parseInt(parts[1]);
  
  return (hours * 60) + minutes;
}

export function minutesToTime(value) {
  let minutes = Math.round(value);
  let hours = Math.floor(minutes/60);
  let r_minutes = minutes - (hours*60);
  
  let time = '';
  if (hours < 10) time += '0';
  time += hours;
  time += ':';
  if (r_minutes < 10) time += '0';
  time += r_minutes;
  
  return time;
}

export function getFieldIfSet(object, field, defaultValue) {
  return (object[field] != undefined ? object[field] : defaultValue);
}