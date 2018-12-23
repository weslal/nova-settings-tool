import {Tabs, Tab} from 'vue-tabs-component'
import {dom, library} from '@fortawesome/fontawesome-svg-core'
import {fas} from '@fortawesome/free-solid-svg-icons'
import {fab} from '@fortawesome/free-brands-svg-icons'
import {far} from '@fortawesome/free-regular-svg-icons'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'

Nova.booting((Vue, router) => {
    Vue.component('tabs', Tabs);
    Vue.component('tab', Tab);
    Vue.component('font-awesome-icon', FontAwesomeIcon);
    library.add(fas, fab, far);
    dom.watch();
    router.addRoutes([
        {
            name: 'settings',
            path: '/settings',
            component: require('./components/Tool'),
        },
    ]);
});
