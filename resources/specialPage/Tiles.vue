<template>
	<div id="unifiedTaskOverview-tiles" class="task-overview">
		<hr>
		<p v-if="noTaskDesc">{{ $i18n( 'unifiedtaskoverview-label-no-task' ) }}</p>
		<ul class="ul-tiles">
			<transition appear name="list" v-for="item in items" v-bind:key="item.id">
				<single-tile
					v-show="item.isVisible"
					v-bind:type="item.type"
					v-bind:header="item.header"
					v-bind:subheader="item.subheader"
					v-bind:body="item.body"
					v-bind:url="item.url"
				>
				</single-tile>
			</transition>
		</ul>
	</div>
</template>

<script>
var SingleTile = require( './SingleTile.vue' );

	module.exports = {
		name: 'TileView',
		props: {
			items: Array,
			noTaskDesc: String
		},
		components: {
			'single-tile': SingleTile
		}
	};
</script>

<style lang="less">

ul {
	list-style-type: none;
	padding: 0;
}
.ul-tiles {
	margin-top: 2rem;
	margin-left: 0;
	display: flex;
	flex-flow: row wrap;
	position: relative;
}

.list-enter,
.list-leave-to {
	visibility: hidden;
	height: 0;
	margin: 0;
	padding: 0;
	opacity: 0;
}

.list-enter-active,
.list-leave-active {
	transition: all ease-out 250ms;
}
</style>