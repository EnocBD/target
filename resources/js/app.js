import './bootstrap';
import * as bootstrap from 'bootstrap';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, Thumbs } from 'swiper/modules';
import 'swiper/swiper-bundle.css';
import 'magnific-popup/dist/magnific-popup.css';
import './cart';
import './magnific-init.js';

// Register Swiper modules
Swiper.use([Navigation, Pagination, Autoplay, Thumbs]);

window.bootstrap = bootstrap;
window.Swiper = Swiper;
