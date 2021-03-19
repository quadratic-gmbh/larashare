export const IMG_SZ_50 = 1;
export const IMG_SZ_150 = 2;
export const IMG_SZ_300 = 3;
export const IMG_SZ_1000 = 4;
export function urlBikeImg(imgId, size) {
  let path = kelAppUrl + '/storage/images/';
  
  switch(size) {
    case IMG_SZ_50:
      path += 50;
      break;
    case IMG_SZ_300:
      path += 300;
      break;
    case IMG_SZ_1000:
      path += 1000;
      break;
    default:
      path += 150;
  }

  path += `/${imgId}.jpg`;
  
  return path;
}

export function urlBikeImgDefault() {
  return kelAppUrl + '/img/bike_square.svg';
}

export function urlBikeShow(bikeId, date) {
  let url = kelAppUrl + '/bike/' + bikeId;
  if (date) {
    url += '?date=' + date;
  }
  return url;
}

export function urlBikeTos(bikeId) {
  return kelAppUrl + '/bike/' + bikeId + '/download_tos';
}

export function urlAsset(asset) {
  return kelAppUrl + '/client/' + asset;
}
