import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './styles/components/alert.css';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './js/alert.js';

import { initLivroForm } from './js/livro-form.js';

document.addEventListener('DOMContentLoaded', () => {
    initLivroForm();
});
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
