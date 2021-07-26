import Vue from 'vue';
import Router from 'vue-router';
//import store from './Store';

Vue.use(Router);

const router = new Router({
    routes: [

        { path: '/', redirect: '/dashboard', },

        // Contributions
        /*{
            path: '/contributions',
            name: 'contributions',
            component: require ('./../contributions/contributions').default,
        },*/

        // Dashboard
        {
            path: '/dashboard',
            name: 'dashboard',
            component: require ('./../pages/dashboard').default,
        },

        // ----------------------------------------------------------------------------------------------
        // Primary Entities

        // New
        {
            path: '/(coins|types)/edit',
            name: 'coins-types-new',
            component: require('./../pages/entities_primary/edit/editNew').default,
            props: (route) => ({
                routed: true,
                entity: route.params.pathMatch
            })
        },

        // Edit
        {
            path: '/(coins|types)/edit/:id/:section?',
            name: 'coins-types-edit',
            component: require('./../pages/entities_primary/edit/index').default,
            props: (route) => ({
                routed: true,
                entity: route.params.pathMatch,
                id: parseInt(route.params.id),
                section: route.params.section
            })
        },

        // Show
        /*{
            path: '/types/show/:id',
            name: 'types-details',
            component: require('./../pages/entities_primary/ItemDetails').default,
            props: (route) => ({ entity: 'types', id: parseInt(route.params.id) })
        },
        {
            path: '/coins/show/:id',
            name: 'coins-details',
            component: require('./../pages/entities_primary/ItemDetails').default,
            props: (route) => ({ entity: 'coins', id: parseInt(route.params.id) })
        },*/
        {
            path: '/(coins|types)/show/:id',
            name: 'coins-types-show',
            component: require('./../pages/entities_primary/show/index').default,
            props: (route) => ({
                entity: route.params.pathMatch,
                id: parseInt(route.params.id)
            })
        },

        // Search
        {
            path: '/(coins|types)/(search|publish)',
            name: 'coins-types-search',
            component: require('./../pages/entities_primary/search/index').default,
            props: (route) => ({
                dialog: false,
                entity: route.params.pathMatch,
                publisher: route.params["1"] === 'publish',
                routedQuery: route.query ?? {}
            })
        },

        // Copy
        {
            path: '/types/copy/:source/:id',
            name: 'types-copy',
            component: require('./../pages/entities_primary/ItemCopy').default,
            props: { entity: 'types'}
        },
        {
            path: '/coins/copy/:source/:id',
            name: 'coins-copy',
            component: require('./../pages/entities_primary/ItemCopy').default,
            props: { entity: 'coins'}
        },

        // import
        {
            path: '/types/import',
            name: 'types-import',
            component: require('./../pages/entities_primary/ItemImport').default,
            props: { entity: 'types'}
        },
        {
            path: '/coins/import',
            name: 'coins-import',
            component: require('./../pages/entities_primary/ItemImport').default,
            props: { entity: 'coins'}
        },


        // ----------------------------------------------------------------------------------------------
        // Secondary Entities
        {
            path: '/denominations/:id?',
            name: 'denominations',
            component: require ('./../pages/entities_secondary/denominations').default,
        },
        {
            path: '/designs/:id?',
            name: 'designs',
            component: require ('./../pages/entities_secondary/designs').default,
        },
        {
            path: '/dies/:id?',
            name: 'dies',
            component: require ('./../pages/entities_secondary/dies').default,
        },
        {
            path: '/findspots/:id?',
            name: 'findspots',
            component: require ('./../pages/entities_secondary/findspots').default,
        },
        {
            path: '/hoards/:id?',
            name: 'hoards',
            component: require ('./../pages/entities_secondary/hoards').default,
        },
        {
            path: '/legends/:id?',
            name: 'legends',
            component: require ('./../pages/entities_secondary/legends').default,
        },
        {
            path: '/legends-index',
            name: 'legends-index',
            component: require ('./../pages/entities_secondary/legendsIndex').default,
        },
        {
            path: '/materials/:id?',
            name: 'materials',
            component: require ('./../pages/entities_secondary/materials').default,
        },
        {
            path: '/mints/:id?',
            name: 'mints',
            component: require ('./../pages/entities_secondary/mints').default,
        },
        {
            path: '/monograms/:id?',
            name: 'monograms',
            component: require ('./../pages/entities_secondary/monograms').default,
        },
        {
            path: '/owners/:id?',
            name: 'owners',
            component: require ('./../pages/entities_secondary/owners').default,
        },
        {
            path: '/periods/:id?',
            name: 'periods',
            component: require ('./../pages/entities_secondary/periods').default,
        },
        {
            path: '/persons/:id?',
            name: 'persons',
            component: require ('./../pages/entities_secondary/persons').default,
        },
        {
            path: '/regions/:id?',
            name: 'regions',
            component: require ('./../pages/entities_secondary/regions').default,
        },
        {
            path: '/standards/:id?',
            name: 'standards',
            component: require ('./../pages/entities_secondary/standards').default,
        },
        {
            path: '/symbols/:id?',
            name: 'symbols',
            component: require ('./../pages/entities_secondary/symbols').default,
        },
        {
            path: '/tribes/:id?',
            name: 'tribes',
            component: require ('./../pages/entities_secondary/tribes').default,
        },


        // ----------------------------------------------------------------------------------------------
        // Tools
        {
            path: '/bibliography/:id?',
            name: 'bibliography',
            component: require ('./../pages/tools/bibliography').default,
        },
        {
            path: '/brokenlinks',
            name: 'brokenlinks',
            component: require ('./../pages/tools/brokenlinks').default,
        },
        {
            path: '/objectgroups/:id?',
            name: 'objectgroups',
            component: require ('./../pages/tools/objectgroups').default,
        },
        {
            path: '/files',
            name: 'files',
            component: require ('./../modules/files').default,
        },
        {
            path: '/storage*',
            name: 'storage',
            component: require ('./../pages/storage/index').default,
        },
        {
            path: '/sparql',
            name: 'sparql',
            component: require ('./../pages/tools/sparql').default,
        },


        // ----------------------------------------------------------------------------------------------
        // Public Relations
        {
            path: '/news',
            name: 'news',
            component: require ('./../pages/website/news').default,
        },
        {
            path: '/coin-of-the-month',
            name: 'coinofthemonth',
            component: require ('./../pages/website/coinofthemonth').default,
        },


        // ----------------------------------------------------------------------------------------------
        // Website
        {
            path: '/team',
            name: 'team',
            component: require ('./../pages/website/team').default,
        },
        {
            path: '/texts',
            name: 'texts',
            component: require ('./../pages/website/texts').default,
        },


        // ----------------------------------------------------------------------------------------------
        // Administrator
        {
            path: '/users/:id?',
            name: 'users',
            component: require ('./../pages/administrator/users').default,
        },
        {
            path: '/logs',
            name: 'logs',
            component: require ('./../pages/administrator/logs').default,
        },
        {
            path: '/test',
            name: 'test',
            component: require ('./../pages/administrator/test').default,
        },


        // ----------------------------------------------------------------------------------------------
        // 404
        {
            path: '*',
            name: 'not-found',
            component: require ('./../pages/404').default,
            props: (route) => ({ path: route.params.pathMatch })
        },
    ],
});


/*router.beforeEach((to, from, next) => {
    store.commit('showLoader');
    next();
});

router.afterEach((to, from) => {
    setTimeout(()=>{
        store.commit('hideLoader');
    },1000);
});*/

export default router;
