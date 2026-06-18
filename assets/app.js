import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

import './styles/app.css';
import './styles/components/alert.css';
import './styles/components/forms.css';
import './styles/components/table.css';

import './js/alert.js';

import { initLivroForm } from './js/livro-form.js';

document.addEventListener('turbo:load', () => {
    console.log('turbo load');
    initLivroForm();
});
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
