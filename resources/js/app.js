import './bootstrap';
import * as bootstrap from 'bootstrap';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/swiper-bundle.css';
import './cart';

// Register Swiper modules
Swiper.use([Navigation, Pagination, Autoplay]);

window.bootstrap = bootstrap;
window.Swiper = Swiper;
