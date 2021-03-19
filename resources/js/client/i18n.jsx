import i18n from 'i18next';
import Backend from 'i18next-xhr-backend';
import { initReactI18next } from 'react-i18next';

const fallbackLng = ['de']; 
const availableLanguages = ['de'];

i18n.use(Backend)
.use(initReactI18next)
.init({
  fallbackLng,
  lng: "de",
  whitelist: availableLanguages,
  backend: {
    loadPath: window.kelApiUrl +  '/client/locales/{{lng}}/{{ns}}.json',
    crossDomain: true
  },   
  interpolation: {
    escapeValue: false
  },
});

export default i18n;