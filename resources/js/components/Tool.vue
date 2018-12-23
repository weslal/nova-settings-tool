<template>
    <div>
        <heading v-if="config.show_title" class="mb-6">{{ translations['settings_title'] }}</heading>
        <tabs v-if="installed && settings && settings.groups" ref="tabs">
            <tab v-for="group in settings.groups" v-bind:data="group" v-bind:key="group.key" v-bind:name="group.name" v-bind:id="group.key" v-bind:prefix="config.show_icons ? '<i class=\'tab-icon ' + group.icon + '\'></i> ' : ''" v-bind:suffix="config.show_suffix ? ' ' + translations['setting_tab_suffix'] : ''">
                <div class="card overflow-hidden">
                    <form @submit.prevent="updateSettings">
                        <div v-for="setting in group.items">
                            <component
                                    v-bind:is="'form-' + setting.field.component"
                                    v-bind:class="`form-${setting.field.textAlign}`"
                                    v-bind:field="setting.field"
                                    v-bind:v-model="setting.value"
                                    ref="components"
                            />
                        </div>
                        <div class="bg-30 flex px-8 py-4">
                            <button type="submit" class="ml-auto btn btn-default btn-primary mr-3">
                                {{ translations['save_settings'] }}
                            </button>
                        </div>
                    </form>
                </div>
            </tab>
        </tabs>
    </div>
</template>

<script>
export default {
    data() {
        return {
            settings: [],
            installed: false,
            reset: true
        }
    },
    mounted() {
        this.installationCheck();
    },
    updated() {
        let children = this.$refs.tabs && this.$refs.tabs.$children ? this.$refs.tabs.$children.length : 0;
        if (children > 0 && (this.$refs.tabs.activeTabHash === '' || this.reset === true)) {
            this.reset = false;
            let hash = this.$refs.tabs.tabs[0].hash;
            this.$refs.tabs.selectTab(hash);
        }
    },
    computed: {
        config: function() {
            return Nova.config.settings_tool.config;
        },
        translations: function() {
            return Nova.config.settings_tool.translations;
        }
    },
    methods: {
        installationCheck() {
            Nova.request()
                .get("/nova-vendor/settings/installed")
                .then(response => {
                    this.installed = response.data.installed;
                    if (this.installed) {
                        this.getSettings();
                    } else {
                        this.$toasted.error(this.translations['module_not_migrated'], {
                            duration : 3000
                        });
                    }
                })
                .catch(error => {
                    this.$toasted.error(this.translations['load_error'], {
                        duration : 3000
                    });
                });
        },
        getSettings() {
            Nova.request()
                .get('/nova-vendor/settings/get').then(response => {
                    this.settings = response.data;
                })
                .catch(error => {
                    this.$toasted.error(this.translations['load_error'], {
                        duration : 3000
                    });
                });
        },
        updateSettings() {
            let values = this.obtainValues();
            Nova.request()
                .put("/nova-vendor/settings/update", {values: values})
                .then(response => {
                    this.settings = response.data.settings;
                    let message = response.data.message && response.data.message !== ''
                        ? response.data.message
                        : this.translations['save_success'];
                    this.$toasted.success(message, {
                        duration : 3000
                    })
                })
                .catch(error => {
                    this.$toasted.error(this.translations['save_error'], {
                        duration : 3000
                    });
                });
        },
        obtainValues() {
            let components = this.$refs.components ? this.$refs.components : [];
            let values = {};
            for (let index in components) {
                values[components[index].fieldAttribute] = components[index].value;
            }
            return values;
        }
    },
}
</script>

<style scoped>
.tab-icon {
    margin-right: 0.4rem;
    height: 0.8rem;
}
</style>
