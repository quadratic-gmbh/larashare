window._ = require('lodash');
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = require('axios');
window.axios.defaults.crossDomain = true;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
/**
 * api url as defined in .env file 
 */
window.kelAppUrl = process.env.MIX_APP_URL;
window.kelApiUrl = process.env.MIX_API_URL;
