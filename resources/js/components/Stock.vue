<template>
    <div class="row">
        <div class="col-12">
            <form class="form-inline" v-on:submit.prevent="stockQuote">
                <div class="form-group">
                    <label for="inputPassword6">Stock Symbol</label>
                    <input type="text" v-model.trim='symbol' class="form-control mx-sm-3">
                </div>
                <button type="submit" class="btn btn-primary my-1">Submit</button>
            </form>
            <br>
        </div>

        <div class="col col-md-3 col-12">
            <div v-bind:class="[dailyChange>0 ? 'border-success' : 'border-danger', 'card']" v-if="showChart">
                <div class="card-header">
                    {{symbol | uppercase}}
                    <span style='float:right' v-bind:class="[dailyChange>0 ? 'text-success' : 'text-danger']">
                        {{dailyQuoteObj['change percent']}}
                        <i v-bind:class="[dailyChange>0 ?'fa-arrow-up' : 'fa-arrow-down', 'fas']"></i>
                    </span>
                </div>
                <div v-bind:class="[dailyChange>0 ? 'text-success' : 'text-danger', 'card-body']">
                    <ul class="list-group list-group-flush">
                        <li v-for="(value, name) in dailyQuoteObj" class="list-group-item" >{{ name }}: {{ value }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class='col-12 col-md-9' id="chart-box" v-if="showChart">
            <div id="chart-candlestick">
                <apexchart type=candlestick height=600 :options="chartOptionsCandlestick" :series="seriesCandle" />
            </div>
        </div>
        <div class="col-12">
            <h3>Query History</h3>
            <hr>
            <crud-vuetable
            ref='history'
            :fetch-url ='crudFetchUrl'
            :base-url = 'BaseUrl'
            :can-search = 'true'
            :can-filter = 'true'
            :can-add = 'false'
            :can-show = 'true'
            :can-edit = 'false'
            :can-remove = 'true'
            :can-copy = 'false'
            :field-configs = "crudFieldConfigs"
            :model-name = "crudModelName"
            :list_row_links_num = 1
            ></crud-vuetable>
        </div>
    </div>
</template>

<script>
class Stock {

    constructor(apiKey) {
        this.apiKey = apiKey;
        this.apiUrl = 'https://www.alphavantage.co/query';
        this.data = null;
    }

    fetchUrl(func, symbol, interval=null, outputsize=null, datatype='json') {
        var url = this.apiUrl+"?function="+func+"&symbol="+symbol;
        if (interval) {
            url += "&interval="+interval;
        }
        if (outputsize) {
            url += "&outputsize="+outputsize;
        }
        url += "&datatype="+datatype+"&apikey="+this.apiKey;
        return url;
    };

    requestData(url, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open( 'GET', url, true );
        xhr.onerror = function( xhr ) { console.log( 'error:', xhr  ); };
        xhr.onprogress = function( xhr ) { console.log( 'bytes loaded:', xhr.loaded  ); }; /// or something
        xhr.onload = callback;
        xhr.send( null );
    };

    // polish key
    polishData(data) {
        let self = this;
        Object.keys(data).forEach(function(key) {
            if (typeof data[key] == 'object') {
                data[key] = self.polishData(data[key]);
                return true;
            }
            let newKey = key.substr(key.indexOf(".")+1).trim();
            data[newKey] = data[key];
            delete data[key];
        })
        return data;
    };

    QuoteEndpoint(symbol, datatype='json', callback=null) {
        const func = 'GLOBAL_QUOTE';
        let self = this;
        this.requestData(this.fetchUrl(func,symbol), function( xhr ) {
            let response;
            response = xhr.target.response;
            response = JSON.parse( response );
            self.data = self.polishData(response['Global Quote']);
            if (callback != null)
                callback(self.data);
        });
    };

    Daily(symbol, outputsize='compact', datatype='json', callback=null) {
        const func = 'TIME_SERIES_DAILY';
        let self = this;
        this.requestData(this.fetchUrl(func,symbol, null, outputsize), function( xhr ) {
            let response;
            response = xhr.target.response;
            response = JSON.parse( response );
            self.data = self.polishData(response['Time Series (Daily)']);
            if (callback != null)
                callback(self.data);
        });
    }
}
import CrudVuetable from './crud/CrudVueTable'
import Form from './Form'
import VueApexCharts from 'vue-apexcharts'

Vue.component('crud-vuetable', CrudVuetable)
Vue.component('vform', Form)
Vue.component('apexchart', VueApexCharts)

export default {
    components: {
        VueApexCharts,
        CrudVuetable,
        Form
    },
    data () {
        return {
            symbol: '',
            chartOptionsCandlestick: {
            title: {
                text: 'CandleStick Chart',
                align: 'left'
            },
            xaxis: {
                type: 'datetime'
            },
            yaxis: {
                tooltip: {
                enabled: true
                }
            }
            },
            seriesCandle:[],
            showChart: false,
            dailyQuoteObj: null
        };
    },
    filters: {
        uppercase: function(v) {
        return v.toUpperCase();
        }
    },
    computed: {
        stockObj: function() {
            return new Stock('0O18XUJW9P8QVGQJ');
        },
        dailyChange: function() {
            return parseFloat(this.dailyQuoteObj['change percent']) / 100.0;
        }
    },
    props: {
        false: {
            type: Boolean,
            default: false
        },
        true: {
            type: Boolean,
            default: true
        },
        crudFetchUrl: {
            type: String,
            required: true
        },
        BaseUrl: {
            type: String,
            required: true
        },
        crudModelName: {
            type: String,
            required: true
        },
        crudFieldConfigs: {
            type: Object,
            required: false
        }
    },
    watch: {
        symbol: function() {
            if (this.symbol == "") {
                this.showChart = false
            }
        }
    },
    methods: {
        stockQuote: function() {
            let self = this;
            this.stockObj.QuoteEndpoint(this.symbol, null, function(){
                axios.post('/stock/quote',{data: self.stockObj.data})
                .then(function(response) {
                    self.dailyQuoteObj = self.stockObj.data;
                    self.$refs.history.$refs.vuetable.refresh();
                }, function(error) {
                    root.setAlert("danger", error.response.data, 0, true);
                });
                self.dailyQuote();
            });
        },
        dailyQuote: function() {
            let self = this;
            self.stockObj.Daily(self.symbol, null, null, function(){
                if (self.stockObj.data != null) {
                    $.each(self.stockObj.data, function(key, val) {
                        self.seriesCandle.push({x:key, y:[val['open'], val['high'], val['low'], val['close']]});
                    });
                    self.seriesCandle = [{data: self.seriesCandle}]
                    self.showChart = true;
                }
            });
        }
    }
}
</script>
