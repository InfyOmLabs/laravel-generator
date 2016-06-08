var Vue = require('vue')
var VueResource = require('vue-resource')
var Vuetable = require('vuetable/src/components/Vuetable.vue')
var VuetablePagination = require('vuetable/src/components/VuetablePagination.vue')
var VuetablePaginationDropdown = require('vuetable/src/components/VuetablePaginationDropdown.vue')
var VuetablePaginationBootstrap = require('vuetable/src/components/VuetablePaginationBootstrap.vue')
var VuetablePaginationSimple = require('../vendor/vue-table/components/VuetablePaginationSimple.vue')
var VueEditable = require('../vendor/vue-editable/vue-editable.js')
var VueStrap = require('../vendor/vue-strap/vue-strap.min.js')
var VueValidator = require('vue-validator')

Vue.use(VueResource)
Vue.use(VueEditable)
Vue.use(VueValidator)

Vue.component('vuetable', Vuetable);
Vue.component('vuetable-pagination', VuetablePagination)
Vue.component('vuetable-pagination-dropdown', VuetablePaginationDropdown)
Vue.component('vuetable-pagination-bootstrap', VuetablePaginationBootstrap)
Vue.component('vuetable-pagination-simple', VuetablePaginationSimple)

var E_SERVER_ERROR = 'Error communicating with the server';

Vue.config.debug = true        

Vue.component('custom-error', {
  props: ['field', 'validator', 'message'],
  template: '<em><p class="error-{{field}}-{{validator}}">{{message}}</p></em>'
});

var vm = new Vue({
    components: {
        modal: VueStrap.modal,
        'v-select': VueStrap.select
    },
    el: "#crud-app",
    data: {
        formModal: false,
        infoModal: false,
        showModal: false,
        deleteModal: false,
        flashMessage: null,
        defaultErrorMessage: 'Some errors in sended data, please check!.',
        flashTypeDanger: 'danger',
        flashType: null,
        submitMessage: "",
        url: apiUrl,           
        row: objectRow,
        searchFor: '',
        columns: tableColumns,     
        sortOrder: {
            field: fieldInitOrder,
            direction: 'asc'
        },
        perPage: 10,
        paginationComponent: 'vuetable-pagination-bootstrap',
        paginationInfoTemplate: 'แสดง {from} ถึง {to} จากทั้งหมด {total} รายการ',
        itemActions: [
            { name: 'view-item', label: '', icon: 'glyphicon glyphicon-zoom-in', class: 'btn btn-info', extra: {'title': 'View', 'data-toggle':"tooltip", 'data-placement': "left"} },
            { name: 'edit-item', label: '', icon: 'glyphicon glyphicon-pencil', class: 'btn btn-warning', extra: {title: 'Edit', 'data-toggle':"tooltip", 'data-placement': "top"} },
            { name: 'delete-item', label: '', icon: 'glyphicon glyphicon-remove', class: 'btn btn-danger', extra: {title: 'Delete', 'data-toggle':"tooltip", 'data-placement': "right" } }
        ],
        moreParams: []                                 
    },
    watch: {
        'perPage': function(val, oldVal) {
            this.$broadcast('vuetable:refresh')
        },
        'paginationComponent': function(val, oldVal) {
            this.$broadcast('vuetable:load-success', this.$refs.vuetable.tablePagination)
            this.paginationConfig(this.paginationComponent)
        }
    },
    methods: {
        submit: function() {
            var actionUrl = this.url.store;
            this.row._token = token;
            if (this.method == 'PATCH' || this.method == 'POST') {
                if (this.method == 'PATCH') {
                    actionUrl = this.url.update + this.row.id;                    
                }  
            } else if (this.method == 'DELETE') {
                actionUrl = this.url.delete + this.row.id;                
            }
            //this.$http({actionUrl, this.method, data}).then(this.success, this.failed);
            this.sendData(actionUrl, this.method, this.row)
                .then(this.success, this.failed);            
        },
        getData: function () {
            this.sendData(this.url.show + this.row.id, 'GET')
                .then(this.success, this.failed);
        },
        sendData: function(url, method, data = {}) {
            return this.$http({url: url, method: method, data: data});
        },            
        cleanData: function() {
            this.row = objectRow;
            this.flashMessage = '';
            this.flashType = '';
        },            
        success: function(response) {
            if (response.data.data) {
                var data = response.data.data;
                vm.$set('row', data);
            }
            if (this.method == 'POST' || this.method == 'PATCH' || this.method == 'DELETE')
                this.$broadcast('vuetable:reload');
            var message = response.data.message;
            vm.flashMessage = message;
            vm.flashType = 'success';
        },
        failed: function(response) {
            vm.flashMessage = vm.defaultErrorMessage;
            vm.flashType = vm.flashTypeDanger;
            if (response.data.errors) {
                vm.updateErrors(response.data.errors);
            }
        },
        updateErrors: function(errors) {
            var errorMessages = [];
            for (var fieldAttr in errors) {
                var errorMgs = errors[fieldAttr];
                for (var msg in errorMgs) {
                    errorMessages.push({ field: fieldAttr, message: errorMgs[msg] });                       
                }
            }
            vm.$setValidationErrors(errorMessages);     
        },
        closeModal: function() {
            this.formModal = this.showModal = this.deleteModal = this.infoModal = false;
            this.cleanData();  
        },
        visible: function(field) {
            for (var column in this.columns) {
                if (this.columns[column].name == field) 
                    return this.columns[column].visible;
            }
            return false;
        },
        modal: function(type) {                    
            this.method = type;
            if (type=='PATCH' || type=='POST') {
                this.formModal = true;
            } else if (type=='SHOW') {
                this.showModal = true;
            } else if (type=='DELETE') {
                this.deleteModal = true;
            } else if (type=='INFO') {
                this.infoModal = true;
            }
        },
        /*
         * Table methods
         */
        setFilter: function() {
            this.moreParams = [
                'filter=' + this.searchFor
            ]
            this.$nextTick(function() {
                this.$broadcast('vuetable:refresh')
            })
        },
        resetFilter: function() {
            this.searchFor = ''
            this.setFilter()
        },
        preg_quote: function( str ) {
            // http://kevin.vanzonneveld.net
            // +   original by: booeyOH
            // +   improved by: Ates Goral (http://magnetiq.com)
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +   bugfixed by: Onno Marsman
            // *     example 1: preg_quote("$40");
            // *     returns 1: '\$40'
            // *     example 2: preg_quote("*RRRING* Hello?");
            // *     returns 2: '\*RRRING\* Hello\?'
            // *     example 3: preg_quote("\\.+*?[^]$(){}=!<>|:");
            // *     returns 3: '\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:'

            return (str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
        },
        highlight: function(needle, haystack) {
            return haystack.replace(
                new RegExp('(' + this.preg_quote(needle) + ')', 'ig'),
                '<span class="highlight">$1</span>'
            )
        },
        paginationConfig: function(componentName) {
            if (componentName == 'vuetable-pagination-dropdown') {
                this.$broadcast('vuetable-pagination:set-options', {
                    wrapperClass: 'form-inline',
                    icons: { prev: 'glyphicon glyphicon-chevron-left', next: 'glyphicon glyphicon-chevron-right' },
                    dropdownClass: 'form-control'
                });
            }
        }                 
    },
    events: {
        'vuetable:row-changed': function(data) {
        },
        'vuetable:row-clicked': function(data, event) {
        },
        'vuetable:cell-dblclicked': function(item, field, event) {
            this.$editable(event, function(value){
                item = JSON.stringify(item);
                var data = JSON.parse(item);  
                data._token = token;
                data[field.name] = value;
                vm.sendData(vm.url.update + data.id, 'PATCH', data).then(
                function (response) {                    
                    event.target.setAttribute("style", "background-color: #f5f5f5");
                }, function (response) {
                    vm.flashMessage = vm.defaultErrorMessage;
                    vm.flashType = vm.flashTypeDanger;
                    if (response.data.errors) {
                        vm.updateErrors(response.data.errors);
                    }
                    vm.modal('INFO');
                    event.target.setAttribute("style", "background-color: red");
                    event.target.setAttribute("title", response.data.errors[field.name]);
                });             
            });
         },
        'vuetable:action': function(action, data) {
            this.cleanData();
            if (action == 'view-item') {
                this.row.id = data.id;
                this.getData();
                this.modal('SHOW');
            } else if (action == 'edit-item') {
                this.row.id = data.id;
                this.getData();
                this.modal('PATCH');                
            } else if (action == 'delete-item') {
                this.row.id = data.id;
                this.modal('DELETE');
            }
        },
        'vuetable:load-success': function(response) {
            var data = response.data.data;
            //onLoadSuccess(data, this.highlight, this.searchFor);
        },
        'vuetable:load-error': function(response) {
            if (response.status == 400) {
                alert(response.data.message)
            } else {
                alert(E_SERVER_ERROR)
            }
        }
    }
});