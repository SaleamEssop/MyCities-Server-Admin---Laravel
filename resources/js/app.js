require('./bootstrap');

import { createApp } from 'vue';
import RegionCostForm from './components/RegionCostForm.vue';

// Mount Vue app only on pages that have the region-cost-app element
const regionCostApp = document.getElementById('region-cost-app');
if (regionCostApp) {
    const app = createApp(RegionCostForm, {
        ...JSON.parse(regionCostApp.dataset.props || '{}')
    });
    app.mount(regionCostApp);
}
