<template>
    <div>
        <input type="text" v-model="keyword" />
        <div class="result-view">
            <ul>
                <li v-for="program in programs" :key="program.id">{{ program.title }}</li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            keyword: "",
            programs: {}
        };
    },
    methods: {
        search() {
            axios
                .get('/api/programs?title=' + this.keyword)
                .then(res => {
                    this.programs = res.data;
                })
                .catch(error => {
                    console.log('取得に失敗しました');
                });
        }
    },
    watch: {
        keyword() {
            this.search();
        }
    }
};
</script>
