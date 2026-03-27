import './bootstrap';

import Alpine from 'alpinejs';
import blogInfiniteScroll from './components/blog-infinite-scroll';

window.Alpine = Alpine;

Alpine.data('blogInfiniteScroll', blogInfiniteScroll);

Alpine.start();
