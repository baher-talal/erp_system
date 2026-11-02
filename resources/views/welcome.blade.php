<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark" data-user-name="{{ auth()->user()->name }}" data-user-role="{{ auth()->user()->role }}" data-user-id="{{ auth()->user()->id }}" data-user-permissions="{{ json_encode(auth()->user()->permissionMatrix()) }}" data-default-permissions="{{ json_encode(config('erp.default_permissions')) }}" data-today="{{ now()->toDateString() }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#f5f7fb">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Talal | Business management</title>
	<style>
		:root {
			--bg: #f5f7fb;
			--surface: #fff;
			--surface-soft: #f8f9fc;
			--text: #20283a;
			--muted: #8790a3;
			--line: #edf0f5;
			--primary: #635bdb;
			--primary-soft: #f0efff;
			--green: #1aa879;
			--green-soft: #e9f8f1;
			--orange: #e99a3b;
			--orange-soft: #fff5e8;
			--red: #e46c72;
			--shadow: 0 12px 32px rgba(39, 48, 75, .045)
		}

		[data-theme="dark"] {
			--bg: #151821;
			--surface: #1d212c;
			--surface-soft: #242936;
			--text: #eef0f6;
			--muted: #9ba3b5;
			--line: #303645;
			--primary: #918aff;
			--primary-soft: #302e4d;
			--green: #53c79c;
			--green-soft: #243d37;
			--orange: #f0b363;
			--orange-soft: #403629;
			--red: #f18a91;
			--shadow: 0 12px 32px rgba(0, 0, 0, .14)
		}

		* {
			box-sizing: border-box
		}

		body {
			margin: 0;
			background: var(--bg);
			color: var(--text);
			font-family: Tahoma, "Segoe UI", Arial, sans-serif;
			font-size: 14px;
			transition: background .2s, color .2s
		}

		button,
		input {
			font: inherit
		}

		button {
			cursor: pointer
		}

		a {
			color: inherit;
			text-decoration: none
		}

		.app {
			min-height: 100vh
		}

		.sidebar {
			position: fixed;
			inset: 0 auto 0 0;
			width: 254px;
			background: var(--surface);
			border-inline-start: 1px solid var(--line);
			padding: 26px 18px;
			display: flex;
			flex-direction: column;
			z-index: 5;
			transition: transform .25s, background .2s;
			overflow-y: auto
		}

		.brand {
			display: flex;
			align-items: center;
			gap: 11px;
			padding: 0 8px 30px
		}

		.brand-mark {
			width: 39px;
			height: 39px;
			border-radius: 13px;
			background: linear-gradient(145deg, #827aff, #5d55d0);
			display: grid;
			place-items: center;
			color: #fff;
			box-shadow: 0 7px 16px #655ce544
		}

		.brand-mark svg {
			width: 22px
		}

		.brand-name {
			font-size: 17px;
			font-weight: 800;
			letter-spacing: -.4px
		}

		.brand-caption {
			font-size: 10px;
			color: var(--muted);
			margin-top: 3px
		}

		.nav-label {
			font-size: 10px;
			font-weight: 700;
			color: #a6adbb;
			padding: 0 12px 10px;
			letter-spacing: .5px
		}

		.nav-list {
			display: grid;
			gap: 4px
		}

		.nav-item {
			height: 42px;
			width: 100%;
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 0 12px;
			border: 0;
			border-radius: 10px;
			background: transparent;
			color: var(--muted);
			text-align: start;
			font-size: 12px;
			font-weight: 600;
			transition: .15s
		}

		.nav-item svg {
			width: 17px;
			height: 17px;
			flex: none
		}

		.nav-item:hover {
			background: var(--surface-soft);
			color: var(--text)
		}

		.nav-item.active {
			color: var(--primary);
			background: var(--primary-soft)
		}

		.nav-item .badge {
			margin-inline-start: auto;
			background: var(--orange-soft);
			color: var(--orange);
			border-radius: 8px;
			padding: 3px 7px;
			font-size: 10px
		}

		.sidebar-spacer {
			flex: 1
		}

		.upgrade-card {
			background: linear-gradient(145deg, #f3f1ff, #f8f7ff);
			border: 1px solid #eeecff;
			border-radius: 13px;
			padding: 14px;
			margin: 15px 3px;
			color: #474382
		}

		.upgrade-card strong {
			font-size: 12px
		}

		.upgrade-card p {
			font-size: 10px;
			line-height: 1.8;
			color: #817da9;
			margin: 6px 0 10px
		}

		.upgrade-card button {
			border: 0;
			border-radius: 7px;
			background: #655cdb;
			color: white;
			font-size: 10px;
			padding: 7px 10px
		}

		.sidebar-foot {
			display: flex;
			align-items: center;
			gap: 10px;
			border-top: 1px solid var(--line);
			padding: 17px 5px 0
		}

		.avatar {
			display: grid;
			place-items: center;
			background: #e8e7ff;
			color: #5d55c9;
			font-weight: 700;
			border-radius: 50%;
			width: 36px;
			height: 36px;
			flex: none;
			font-size: 12px
		}

		.user-name {
			font-size: 11px;
			font-weight: 700
		}

		.user-role {
			font-size: 10px;
			color: var(--muted);
			margin-top: 3px
		}
		.logout-button{display:block;border:0;background:transparent;color:var(--muted);font-size:9px;padding:5px 0 0}

		.main {
			margin-inline-start: 254px;
			min-height: 100vh;
			padding: 0 35px 36px
		}

		.topbar {
			height: 82px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			border-bottom: 1px solid var(--line);
			gap: 20px
		}

		.page-greeting {
			font-size: 18px;
			font-weight: 750
		}

		.page-date {
			font-size: 11px;
			color: var(--muted);
			margin-top: 5px
		}

		.top-actions {
			display: flex;
			align-items: center;
			gap: 10px
		}

		.search-wrap {
			position: relative
		}

		.search-wrap svg {
			position: absolute;
			inset-inline-start: 12px;
			top: 11px;
			width: 16px;
			color: var(--muted)
		}

		.search {
			width: 210px;
			height: 38px;
			border: 1px solid var(--line);
			border-radius: 9px;
			background: var(--surface);
			padding: 0 36px 0 12px;
			color: var(--text);
			outline: none;
			font-size: 11px
		}

		.search:focus {
			border-color: var(--primary)
		}

		.icon-btn {
			width: 38px;
			height: 38px;
			border: 1px solid var(--line);
			border-radius: 9px;
			background: var(--surface);
			color: var(--muted);
			display: grid;
			place-items: center;
			position: relative
		}

		.icon-btn svg {
			width: 17px;
			height: 17px
		}

		.notif-dot {
			position: absolute;
			width: 6px;
			height: 6px;
			border-radius: 50%;
			background: #ef797c;
			top: 8px;
			inset-inline-start: 9px;
			border: 1px solid var(--surface)
		}

		.lang-btn {
			height: 36px;
			min-width: 43px;
			border: 1px solid var(--line);
			border-radius: 9px;
			background: var(--surface);
			color: var(--text);
			font-weight: 700;
			font-size: 11px
		}

		.mobile-menu {
			display: none
		}

		.content {
			max-width: 1450px;
			margin: 0 auto
		}

		.welcome-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin: 26px 0 18px
		}

		.welcome-title {
			font-size: 18px;
			font-weight: 750;
			margin: 0
		}

		.welcome-copy {
			color: var(--muted);
			font-size: 11px;
			margin: 6px 0 0
		}

		.primary-btn {
			border: 0;
			background: var(--primary);
			color: white;
			height: 38px;
			border-radius: 9px;
			padding: 0 15px;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			font-size: 11px;
			font-weight: 700;
			box-shadow: 0 5px 12px #635bdb2b
		}

		.primary-btn svg {
			width: 15px
		}

		.stats-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 15px;
			margin-bottom: 16px
		}

		.stat-card,
		.panel {
			background: var(--surface);
			border: 1px solid var(--line);
			border-radius: 13px;
			box-shadow: var(--shadow)
		}

		.stat-card {
			padding: 17px 18px
		}

		.stat-top {
			display: flex;
			justify-content: space-between;
			align-items: center
		}

		.stat-label {
			font-size: 11px;
			color: var(--muted);
			font-weight: 600
		}

		.stat-icon {
			width: 34px;
			height: 34px;
			border-radius: 10px;
			display: grid;
			place-items: center
		}

		.stat-icon svg {
			width: 17px
		}

		.purple {
			background: var(--primary-soft);
			color: var(--primary)
		}

		.green {
			background: var(--green-soft);
			color: var(--green)
		}

		.orange {
			background: var(--orange-soft);
			color: var(--orange)
		}

		.red {
			background: #fff0f0;
			color: var(--red)
		}

		.stat-value {
			font-size: 24px;
			font-weight: 750;
			margin-top: 12px;
			letter-spacing: -.5px
		}

		.stat-foot {
			font-size: 10px;
			color: var(--muted);
			margin-top: 7px
		}

		.trend {
			color: var(--green);
			font-weight: 700;
			margin-inline-end: 5px
		}

		.dashboard-grid {
			display: grid;
			grid-template-columns: minmax(0, 1.58fr) minmax(270px, .92fr);
			gap: 16px
		}

		.panel {
			padding: 18px;
			margin-bottom: 16px
		}

		.panel-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 16px
		}

		.panel-title {
			font-size: 13px;
			font-weight: 750
		}

		.panel-subtitle {
			font-size: 10px;
			color: var(--muted);
			margin-top: 4px
		}

		.text-link {
			color: var(--primary);
			font-size: 10px;
			font-weight: 700;
			background: none;
			border: 0;
			padding: 5px
		}

		.chart-area {
			height: 190px;
			position: relative;
			padding: 10px 4px 24px 32px
		}

		.chart-grid {
			position: absolute;
			inset: 10px 0 28px 32px;
			display: flex;
			flex-direction: column;
			justify-content: space-between
		}

		.chart-grid span {
			height: 1px;
			border-top: 1px dashed var(--line);
			position: relative
		}

		.chart-grid span:before {
			content: attr(data-value);
			position: absolute;
			inset-inline-start: -30px;
			top: -7px;
			color: var(--muted);
			font-size: 9px
		}

		.chart-svg {
			position: absolute;
			inset: 8px 5px 26px 34px;
			width: calc(100% - 39px);
			height: calc(100% - 34px);
			overflow: visible
		}

		.chart-labels {
			position: absolute;
			bottom: 2px;
			inset-inline: 30px 0;
			display: flex;
			justify-content: space-between;
			color: var(--muted);
			font-size: 9px;
			direction: ltr
		}

		.chart-legend {
			display: flex;
			align-items: center;
			gap: 15px;
			font-size: 9px;
			color: var(--muted)
		}

		.legend-dot {
			display: inline-block;
			width: 7px;
			height: 7px;
			border-radius: 50%;
			margin-inline-end: 5px;
			background: var(--primary)
		}

		.legend-dot.alt {
			background: #a9a4f5
		}

		.projects {
			display: grid;
			gap: 15px
		}

		.project-row {
			display: grid;
			grid-template-columns: minmax(100px, 1.2fr) minmax(80px, 1fr) 42px;
			gap: 13px;
			align-items: center
		}

		.project-name {
			font-size: 10px;
			font-weight: 700;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis
		}

		.project-client {
			font-size: 9px;
			color: var(--muted);
			margin-top: 4px
		}

		.progress-track {
			height: 6px;
			background: var(--surface-soft);
			border-radius: 8px;
			overflow: hidden
		}

		.progress-fill {
			height: 100%;
			border-radius: 8px;
			background: var(--primary)
		}

		.project-progress {
			text-align: end;
			font-size: 9px;
			font-weight: 700;
			color: var(--muted)
		}

		.task-list {
			display: grid
		}

		.task-row {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 11px 0;
			border-bottom: 1px solid var(--line)
		}

		.task-row:last-child {
			border-bottom: 0;
			padding-bottom: 0
		}

		.check {
			width: 16px;
			height: 16px;
			border: 1.5px solid #cbd0dc;
			border-radius: 5px;
			flex: none;
			background: transparent;
			display: grid;
			place-items: center;
			padding: 0
		}

		.check.checked {
			background: var(--primary);
			border-color: var(--primary);
			color: #fff
		}

		.check svg {
			width: 11px
		}

		.task-info {
			min-width: 0;
			flex: 1
		}

		.task-name {
			font-size: 10px;
			font-weight: 650;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis
		}

		.task-meta {
			font-size: 9px;
			color: var(--muted);
			margin-top: 4px
		}

		.task-tag {
			border-radius: 6px;
			padding: 4px 7px;
			font-size: 9px;
			white-space: nowrap
		}

		.tag-high {
			color: var(--red);
			background: #fff0f0
		}

		.tag-mid {
			color: var(--orange);
			background: var(--orange-soft)
		}

		.tag-low {
			color: var(--green);
			background: var(--green-soft)
		}

		.attendance {
			display: grid;
			gap: 12px
		}

		.attendance-row {
			display: flex;
			align-items: center;
			gap: 9px
		}

		.mini-avatar {
			width: 29px;
			height: 29px;
			border-radius: 50%;
			background: #eeeaff;
			color: #6b60d9;
			display: grid;
			place-items: center;
			font-size: 9px;
			font-weight: 700;
			flex: none
		}

		.attendance-name {
			font-size: 10px;
			font-weight: 650;
			flex: 1
		}

		.attendance-role {
			color: var(--muted);
			font-size: 9px;
			margin-top: 3px
		}

		.status {
			font-size: 9px;
			padding: 4px 7px;
			border-radius: 7px
		}

		.status-in {
			color: var(--green);
			background: var(--green-soft)
		}

		.status-late {
			color: var(--orange);
			background: var(--orange-soft)
		}

		.calendar-list {
			display: grid;
			gap: 11px
		}

		.event-row {
			display: flex;
			gap: 10px;
			align-items: center
		}

		.event-date {
			width: 39px;
			height: 39px;
			border-radius: 9px;
			background: var(--primary-soft);
			display: grid;
			place-content: center;
			text-align: center;
			color: var(--primary);
			flex: none
		}

		.event-date strong {
			font-size: 13px
		}

		.event-date span {
			font-size: 8px;
			margin-top: 2px
		}

		.event-name {
			font-size: 10px;
			font-weight: 700
		}

		.event-time {
			font-size: 9px;
			color: var(--muted);
			margin-top: 4px
		}

		.activity-list {
			display: grid;
			gap: 14px
		}

		.activity-item {
			display: flex;
			align-items: flex-start;
			gap: 9px
		}

		.activity-icon {
			width: 26px;
			height: 26px;
			border-radius: 8px;
			background: var(--green-soft);
			color: var(--green);
			display: grid;
			place-items: center;
			flex: none
		}

		.activity-icon svg {
			width: 13px
		}

		.activity-text {
			font-size: 9px;
			line-height: 1.7
		}

		.activity-time {
			font-size: 8px;
			color: var(--muted);
			margin-top: 2px
		}

		.module-view {
			min-height: 420px
		}

		.module-hero {
			padding: 25px;
			border-radius: 13px;
			background: linear-gradient(115deg, var(--primary-soft), var(--surface));
			margin-bottom: 16px
		}

		.module-hero h2 {
			font-size: 20px;
			margin: 0 0 8px
		}

		.module-hero p {
			color: var(--muted);
			font-size: 11px;
			margin: 0
		}

		.module-summary {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 14px;
			margin-bottom: 16px
		}

		.module-summary .stat-card {
			padding: 18px
		}

		.module-empty {
			min-height: 180px;
			display: grid;
			place-content: center;
			text-align: center;
			color: var(--muted);
			font-size: 11px;
			gap: 7px
		}

		.module-empty strong {
			color: var(--text);
			font-size: 13px
		}
		.record-list{display:grid;gap:10px}.record-card{border:1px solid var(--line);border-radius:10px;padding:14px;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:center}.record-title{font-size:12px;font-weight:700;overflow-wrap:anywhere}.record-description,.record-meta{font-size:10px;color:var(--muted);margin-top:5px;line-height:1.7;overflow-wrap:anywhere}.record-actions{display:flex;gap:6px;flex-wrap:wrap}.record-actions button,.record-actions a,.secondary-btn{border:1px solid var(--line);background:var(--surface);color:var(--text);padding:7px 10px;border-radius:7px;font-size:10px}.record-actions .danger{color:#d3545d}.record-status{display:inline-block;margin-top:7px;padding:4px 7px;border-radius:6px;background:var(--primary-soft);color:var(--primary);font-size:9px}.module-toolbar{display:flex;align-items:center;gap:9px;margin-bottom:12px}.module-toolbar input,.module-toolbar select,.form-field input,.form-field select,.form-field textarea{border:1px solid var(--line);background:var(--surface);color:var(--text);border-radius:8px;padding:9px 10px;font-size:11px;width:100%;outline:none}.module-toolbar input:focus,.form-field input:focus,.form-field select:focus,.form-field textarea:focus{border-color:var(--primary)}.module-toolbar select{max-width:180px}.record-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.form-field{display:grid;gap:6px;font-size:10px;color:var(--muted)}.form-field.full{grid-column:1/-1}.form-field textarea{min-height:75px;resize:vertical}.form-actions{grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px}.empty-state{text-align:center;padding:35px 10px;color:var(--muted);font-size:11px}.report-row{display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid var(--line);font-size:11px}.pagination{display:flex;justify-content:center;align-items:center;gap:10px;margin-top:15px;color:var(--muted);font-size:10px}.pagination button{border:1px solid var(--line);background:var(--surface);color:var(--text);border-radius:7px;padding:7px 12px;font-size:10px}
		.permission-section{border:1px solid var(--line);border-radius:9px;padding:12px}.permission-section legend{padding:0 6px;font-weight:700}.permission-grid{display:grid;gap:8px}.permission-row{display:grid;grid-template-columns:minmax(110px,1fr) repeat(3,minmax(90px,auto));gap:10px;align-items:center;border-bottom:1px solid var(--line);padding:7px 0}.permission-row label{display:flex;align-items:center;gap:6px;font-weight:400}.permission-row input{width:auto}

		.toast {
			position: fixed;
			bottom: 22px;
			inset-inline-end: 22px;
			background: #252a38;
			color: #fff;
			padding: 12px 17px;
			border-radius: 9px;
			font-size: 11px;
			box-shadow: var(--shadow);
			z-index: 20;
			opacity: 0;
			transform: translateY(8px);
			transition: .2s;
			pointer-events: none
		}

		.toast.show {
			opacity: 1;
			transform: translateY(0)
		}

		.overlay {
			display: none
		}

		[data-theme="dark"] .upgrade-card {
			background: linear-gradient(145deg, #302e4d, #242637);
			border-color: #3a375a;
			color: #dddaff
		}

		[data-theme="dark"] .upgrade-card p {
			color: #aaa5d4
		}

		[data-theme="dark"] .red {
			background: #432d34
		}

		[data-theme="dark"] .tag-high {
			background: #432d34
		}

		[data-theme="dark"] .stat-icon.orange,
		[data-theme="dark"] .status-late {
			background: var(--orange-soft)
		}

		@media(max-width:1100px) {
			.main {
				padding: 0 23px 30px
			}

			.sidebar {
				width: 225px
			}

			.main {
				margin-inline-start: 225px
			}

			.stats-grid {
				gap: 10px
			}

			.stat-card {
				padding: 15px
			}

			.dashboard-grid {
				grid-template-columns: minmax(0, 1.4fr) minmax(250px, .9fr)
			}
		}

		@media(max-width:820px) {
			.sidebar {
				transform: translateX(110%);
				inset-inline-start: auto;
				inset-inline-end: 0;
				width: 270px;
				border-inline-start: 0;
				border-inline-end: 1px solid var(--line);
				box-shadow: var(--shadow)
			}

			.sidebar.open {
				transform: translateX(0)
			}

			.main {
				margin: 0;
				padding: 0 20px 30px
			}

			.mobile-menu {
				display: grid
			}

			.overlay.show {
				display: block;
				position: fixed;
				inset: 0;
				background: #11182766;
				z-index: 4
			}

			.dashboard-grid {
				grid-template-columns: 1fr 1fr
			}

			.dashboard-grid>.column:first-child {
				grid-column: 1/-1
			}

			.topbar {
				height: 72px
			}
		}

		@media(max-width:580px) {
			.main {
				padding: 0 13px 24px
			}

			.topbar {
				gap: 8px
			}

			.page-greeting {
				font-size: 15px
			}

			.page-date {
				font-size: 9px
			}

			.search-wrap {
				display: none
			}

			.top-actions {
				gap: 6px
			}

			.icon-btn {
				width: 34px;
				height: 34px
			}

			.lang-btn {
				height: 34px;
				min-width: 38px
			}

			.welcome-row {
				align-items: flex-start;
				gap: 12px
			}

			.welcome-title {
				font-size: 15px
			}

			.welcome-copy {
				line-height: 1.7
			}

			.primary-btn {
				height: 35px;
				padding: 0 10px;
				white-space: nowrap;
				font-size: 10px
			}

			.stats-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
				gap: 9px
			}

			.stat-card {
				padding: 13px
			}

			.stat-value {
				font-size: 21px
			}

			.stat-label {
				font-size: 10px
			}

			.dashboard-grid {
				grid-template-columns: 1fr;
				gap: 0
			}

			.dashboard-grid>.column:first-child {
				grid-column: auto
			}

			.panel {
				padding: 15px;
				margin-bottom: 12px
			}

			.module-summary {
				grid-template-columns: 1fr
			}

			.chart-area {
				height: 170px
			}

			.project-row {
				grid-template-columns: minmax(92px, 1.2fr) minmax(60px, 1fr) 36px;
				gap: 8px
			}
		}

		.sidebar {
			inset-block: 0;
			inset-inline-start: 0;
			inset-inline-end: auto
		}
	</style>
</head>

<body>
	<div class="app">
		<div class="overlay" id="overlay"></div>
		<aside class="sidebar" id="sidebar">
			<a class="brand" href="#" aria-label="Talal">
				<span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
						<path d="M5 17.5 12 4l7 13.5h-5.2L12 12l-1.8 5.5H5Z" fill="currentColor" />
						<path d="M8.5 20h7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
					</svg></span>
				<span><span class="brand-name" data-i18n-key="Talal">Talal</span><span class="brand-caption" data-i18n-key="Business management">Business management</span></span>
			</a>
			<div class="nav-label" data-i18n-key="MAIN MENU">MAIN MENU</div>
			<nav class="nav-list" aria-label="القائمة الرئيسية">
				<button class="nav-item active" data-module="overview"><svg viewBox="0 0 24 24" fill="none"
						stroke="currentColor" stroke-width="1.7">
						<rect x="3" y="3" width="8" height="8" rx="2" />
						<rect x="13" y="3" width="8" height="5" rx="2" />
						<rect x="13" y="10" width="8" height="11" rx="2" />
						<rect x="3" y="13" width="8" height="8" rx="2" />
					</svg><span data-i18n-key="Overview">Overview</span></button>
				<button class="nav-item" data-module="employees"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
						<circle cx="9" cy="7" r="4" />
						<path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
					</svg><span data-i18n-key="Employees">Employees</span></button>
				<button class="nav-item" data-module="departments"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<rect x="3" y="4" width="18" height="16" rx="2" />
						<path d="M8 8h8M8 12h8M8 16h4" />
					</svg><span data-i18n-key="Departments">Departments</span></button>
				<button class="nav-item" data-module="tasks"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<path d="M9 11 12 14 22 4" />
						<path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
					</svg><span data-i18n-key="Tasks">Tasks</span><span class="badge" id="taskBadge">0</span></button>
				<button class="nav-item" data-module="projects"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<rect x="3" y="4" width="18" height="16" rx="2" />
						<path d="M7 8h10M7 12h6M7 16h3M8 4V2m8 2V2" />
					</svg><span data-i18n-key="Projects">Projects</span></button>
				<button class="nav-item" data-module="clients"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
						<circle cx="9" cy="7" r="4" />
						<path d="M19 8v6m3-3h-6" />
					</svg><span data-i18n-key="Clients">Clients</span></button>
				<button class="nav-item" data-module="payments"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<rect x="2" y="5" width="20" height="14" rx="2" />
						<path d="M2 10h20m-15 5h4" />
					</svg><span data-i18n-key="Payments">Payments</span></button>
				<button class="nav-item" data-module="files"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
						<path d="M14 2v6h6M8 13h8m-8 4h8" />
					</svg><span data-i18n-key="Files">Files</span></button>
				<button class="nav-item" data-module="reports"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<path d="M3 3v18h18" />
						<path d="m19 9-5 5-4-4-5 5" />
						<path d="M15 9h4v4" />
					</svg><span data-i18n-key="Reports &amp; analytics">Reports & analytics</span></button>
			</nav>
			<div class="nav-label" style="margin-top:22px" data-i18n-key="WORKSPACE">WORKSPACE</div>
			<nav class="nav-list" aria-label="مساحة العمل">
				<button class="nav-item" data-module="chat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<path
							d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 4a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z" />
					</svg><span data-i18n-key="Internal chat">Internal chat</span></button>
				<button class="nav-item" data-module="attendance"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<circle cx="12" cy="12" r="9" />
						<path d="M12 7v5l3 2" />
					</svg><span data-i18n-key="Attendance">Attendance</span></button>
				<button class="nav-item" data-module="salary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<rect x="3" y="5" width="18" height="14" rx="2" />
						<path d="M16 12h.01M7 12h4m-4 3h7" />
					</svg><span data-i18n-key="Payroll">Payroll</span></button>
				<button class="nav-item" data-module="calendar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<rect x="3" y="5" width="18" height="16" rx="2" />
						<path d="M16 3v4M8 3v4M3 10h18m-13 4h2m4 0h2m-8 3h2" />
					</svg><span data-i18n-key="Calendar">Calendar</span></button>
				<button class="nav-item" data-module="inventory"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<path d="m21 8-9-5-9 5v8l9 5 9-5V8Z" />
						<path d="m3.3 7.8 8.7 5 8.7-5M12 22V13m-4-7 9 5" />
					</svg><span data-i18n-key="Inventory">Inventory</span></button>
				<button class="nav-item" data-module="personal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
						stroke-width="1.7">
						<path d="M9 11 12 14 22 4" />
						<path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
					</svg><span data-i18n-key="My to-do list">My to-do list</span></button>
			</nav>
			<div class="sidebar-spacer"></div>
			<div class="upgrade-card"><strong data-i18n-key="Your workspace is ready">Your workspace is ready</strong>
				<p data-i18n-key="Use the menu to access your team tools and company data.">Use the menu to access your team tools and company data.</p>
			</div>
			<div class="sidebar-foot"><span class="avatar">{{ substr(auth()->user()->name, 0, 1) }}</span><span><span class="user-name">{{ auth()->user()->name }}</span><span class="user-role" id="userRoleLabel" data-role-label="{{ auth()->user()->role }}">{{ ucfirst(auth()->user()->role) }}</span></span><form method="POST" action="{{ url('/logout') }}">@csrf<button class="logout-button" type="submit" data-i18n-key="Sign out">Sign out</button></form></div>
		</aside>
		<main class="main">
			<header class="topbar">
				<div style="display:flex;align-items:center;gap:11px">
					<button class="icon-btn mobile-menu" id="menuToggle" aria-label="Open menu"><svg viewBox="0 0 24 24"
							fill="none" stroke="currentColor" stroke-width="1.8">
							<path d="M4 6h16M4 12h16M4 18h16" />
						</svg></button>
					<div>
						<div class="page-greeting" id="pageGreeting">{{ auth()->user()->name }}</div>
						<div class="page-date" id="pageDate"></div>
					</div>
				</div>
				<div class="top-actions">
					<label class="search-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
							<circle cx="11" cy="11" r="7" />
							<path d="m20 20-4-4" />
						</svg><input class="search" id="searchInput" type="search" placeholder="Search anything..."
							data-i18n-placeholder-key="Search anything..."></label>
					<button class="icon-btn" id="themeToggle" aria-label="Toggle theme" title="Toggle theme"><svg
							viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
							<path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z" />
						</svg></button>
					<button class="icon-btn" id="notificationButton" aria-label="Notifications"><svg viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="1.7">
							<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4" />
						</svg><span class="notif-dot"></span></button>
					<button class="lang-btn" id="languageToggle" aria-label="Switch language">EN</button>
				</div>
			</header>
			<div class="content">
				<section id="overviewPage">
					<div class="welcome-row">
						<div>
							<h1 class="welcome-title" data-i18n-key="Overview">Overview</h1>
							<p class="welcome-copy" data-i18n-key="Here&#x27;s a quick look at what&#x27;s happening across your company today.">Here's a quick look at what's happening across your company today.</p>
						</div>
						<button class="primary-btn" id="newTaskButton"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
								stroke-width="2">
								<path d="M12 5v14m-7-7h14" />
							</svg><span data-i18n-key="New task">New task</span></button>
					</div>
					<div class="stats-grid">
						<article class="stat-card">
							<div class="stat-top"><span class="stat-label" data-i18n-key="Total employees">Total employees</span><span class="stat-icon purple"><svg viewBox="0 0 24 24" fill="none"
										stroke="currentColor" stroke-width="1.7">
										<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
										<circle cx="9" cy="7" r="4" />
										<path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
									</svg></span></div>
							<div class="stat-value">248</div>
							<div class="stat-foot"><span class="trend">↗ 12%</span><span data-i18n-key="vs. last month">vs. last month</span></div>
						</article>
						<article class="stat-card">
							<div class="stat-top"><span class="stat-label" data-i18n-key="Active projects">Active projects</span><span class="stat-icon green"><svg viewBox="0 0 24 24"
										fill="none" stroke="currentColor" stroke-width="1.7">
										<rect x="3" y="4" width="18" height="16" rx="2" />
										<path d="M7 8h10M7 12h6M7 16h3" />
									</svg></span></div>
							<div class="stat-value">18</div>
							<div class="stat-foot"><span class="trend">↗ 3</span><span data-i18n-key="new this month">new this month</span></div>
						</article>
						<article class="stat-card">
							<div class="stat-top"><span class="stat-label" data-i18n-key="Completed tasks">Completed tasks</span><span class="stat-icon orange"><svg viewBox="0 0 24 24" fill="none"
										stroke="currentColor" stroke-width="1.7">
										<path d="m9 11 3 3L22 4" />
										<path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
									</svg></span></div>
							<div class="stat-value">86<span style="font-size:13px;color:var(--muted);font-weight:500">/ 104</span>
							</div>
							<div class="stat-foot"><span class="trend">↗ 8.4%</span><span data-i18n-key="completion this week">completion this week</span></div>
						</article>
						<article class="stat-card">
							<div class="stat-top"><span class="stat-label" data-i18n-key="Revenue this month">Revenue this month</span><span class="stat-icon red"><svg
										viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
										<rect x="2" y="5" width="20" height="14" rx="2" />
										<path d="M2 10h20m-15 5h4" />
									</svg></span></div>
							<div class="stat-value" style="font-size:22px">٢٤٨,٥٠٠ <span
									style="font-size:11px;color:var(--muted);font-weight:500" data-i18n-key="LE">LE</span></div>
							<div class="stat-foot"><span class="trend">↗ 18.2%</span><span data-i18n-key="vs. last month">vs. last month</span></div>
						</article>
					</div>
					<div class="dashboard-grid">
						<div class="column">
							<section class="panel">
								<div class="panel-head">
									<div>
										<div class="panel-title" data-i18n-key="Revenue overview">Revenue overview</div>
										<div class="panel-subtitle" data-i18n-key="Revenue performance over the last 6 months">Revenue performance over the last 6 months</div>
									</div>
									<div class="chart-legend"><span><i class="legend-dot"></i><span data-i18n-key="This year">This year</span></span><span><i class="legend-dot alt"></i><span data-i18n-key="Last year">Last year</span></span></div>
								</div>
								<div class="chart-area">
									<div class="chart-grid"><span data-value="300k"></span><span data-value="200k"></span><span
											data-value="100k"></span><span data-value="0"></span></div><svg class="chart-svg"
										viewBox="0 0 600 145" preserveAspectRatio="none" aria-label="Revenue chart">
										<defs>
											<linearGradient id="fillRevenue" x1="0" x2="0" y1="0" y2="1">
												<stop offset="0%" stop-color="#7168e8" stop-opacity=".2" />
												<stop offset="100%" stop-color="#7168e8" stop-opacity="0" />
											</linearGradient>
										</defs>
										<path
											d="M0 115 C35 108 47 89 85 94 S130 104 170 77 S218 69 255 83 S304 73 342 52 S395 66 427 45 S474 52 510 27 S562 37 600 9 L600 145 L0 145Z"
											fill="url(#fillRevenue)" />
										<path
											d="M0 115 C35 108 47 89 85 94 S130 104 170 77 S218 69 255 83 S304 73 342 52 S395 66 427 45 S474 52 510 27 S562 37 600 9"
											fill="none" stroke="#7168e8" stroke-width="3" vector-effect="non-scaling-stroke" />
										<path
											d="M0 128 C40 119 52 125 85 112 S136 122 170 108 S220 96 255 110 S307 101 342 89 S394 102 427 83 S480 92 510 72 S563 81 600 59"
											fill="none" stroke="#aaa5f5" stroke-width="2" stroke-dasharray="5 5"
											vector-effect="non-scaling-stroke" />
									</svg>
									<div class="chart-labels">
										<span>May</span><span>Jun</span><span>Jul</span><span>Aug</span><span>Sep</span><span>Oct</span>
									</div>
								</div>
							</section>
							<section class="panel">
								<div class="panel-head">
									<div>
										<div class="panel-title" data-i18n-key="Current projects">Current projects</div>
										<div class="panel-subtitle" data-i18n-key="Keep track of your team&#x27;s progress">Keep track of your team's progress</div>
									</div><button class="text-link" data-module-link="projects" data-i18n-key="View all">View all</button>
								</div>
								<div class="projects">
									<div class="project-row">
										<div>
											<div class="project-name" data-i18n-key="Mobile app development">Mobile app development</div>
											<div class="project-client" data-i18n-key="Almadar Technology">Almadar Technology</div>
										</div>
										<div class="progress-track">
											<div class="progress-fill" style="width:76%"></div>
										</div>
										<div class="project-progress">76%</div>
									</div>
									<div class="project-row">
										<div>
											<div class="project-name" data-i18n-key="Website redesign">Website redesign</div>
											<div class="project-client" data-i18n-key="Horizon Group">Horizon Group</div>
										</div>
										<div class="progress-track">
											<div class="progress-fill" style="width:52%;background:#35b88c"></div>
										</div>
										<div class="project-progress">52%</div>
									</div>
									<div class="project-row">
										<div>
											<div class="project-name" data-i18n-key="Resource management platform">Resource management platform</div>
											<div class="project-client" data-i18n-key="Nuqta Co.">Nuqta Co.</div>
										</div>
										<div class="progress-track">
											<div class="progress-fill" style="width:34%;background:#e8a64d"></div>
										</div>
										<div class="project-progress">34%</div>
									</div>
									<div class="project-row">
										<div>
											<div class="project-name" data-i18n-key="Q4 marketing campaign">Q4 marketing campaign</div>
											<div class="project-client" data-i18n-key="Internal">Internal</div>
										</div>
										<div class="progress-track">
											<div class="progress-fill" style="width:88%;background:#e87980"></div>
										</div>
										<div class="project-progress">88%</div>
									</div>
								</div>
							</section>
							<section class="panel">
								<div class="panel-head">
									<div>
										<div class="panel-title" data-i18n-key="My tasks">My tasks</div>
										<div class="panel-subtitle" data-i18n-key="You have 4 tasks to complete">You have 4 tasks to complete</div>
									</div><button class="text-link" data-module-link="tasks" data-i18n-key="View all">View all</button>
								</div>
								<div class="task-list" id="taskList">
									<div class="task-row"><button class="check" aria-label="Complete task"></button>
										<div class="task-info">
											<div class="task-name" data-i18n-key="Review project proposal for client">Review project proposal for client</div>
											<div class="task-meta" data-i18n-key="Mobile app project · Today">Mobile app project · Today</div>
										</div><span class="task-tag tag-high" data-i18n-key="High">High</span>
									</div>
									<div class="task-row"><button class="check" aria-label="Complete task"></button>
										<div class="task-info">
											<div class="task-name" data-i18n-key="Send monthly performance report">Send monthly performance report</div>
											<div class="task-meta" data-i18n-key="Marketing team · Today">Marketing team · Today</div>
										</div><span class="task-tag tag-mid" data-i18n-key="Medium">Medium</span>
									</div>
									<div class="task-row"><button class="check" aria-label="Complete task"></button>
										<div class="task-info">
											<div class="task-name" data-i18n-key="Approve leave request">Approve leave request</div>
											<div class="task-meta" data-i18n-key="Human resources · Tomorrow">Human resources · Tomorrow</div>
										</div><span class="task-tag tag-low" data-i18n-key="Low">Low</span>
									</div>
								</div>
							</section>
						</div>
						<div class="column">
							<section class="panel">
								<div class="panel-head">
									<div>
										<div class="panel-title" data-i18n-key="Team attendance">Team attendance</div>
										<div class="panel-subtitle" data-i18n-key="186 employees checked in today">186 employees checked in today</div>
									</div><button class="text-link" data-module-link="attendance" data-i18n-key="Details">Details</button>
								</div>
								<div class="attendance">
									<div class="attendance-row"><span class="mini-avatar">سع</span><span style="flex:1"><span
												class="attendance-name" data-i18n-key="Sarah Alabdullah">Sarah Alabdullah</span><span class="attendance-role" data-i18n-key="Product design">Product design</span></span><span class="status status-in" data-i18n-key="Present · 8:02">Present · 8:02</span></div>
									<div class="attendance-row"><span class="mini-avatar"
											style="background:#e4f6ed;color:#26956e">خا</span><span style="flex:1"><span
												class="attendance-name" data-i18n-key="Khalid Alharbi">Khalid Alharbi</span><span
												class="attendance-role" data-i18n-key="Software development">Software development</span></span><span class="status status-in" data-i18n-key="Present · 8:15">Present · 8:15</span></div>
									<div class="attendance-row"><span class="mini-avatar"
											style="background:#fff0e1;color:#c98531">نو</span><span style="flex:1"><span
												class="attendance-name" data-i18n-key="Nora Alshahri">Nora Alshahri</span><span
												class="attendance-role" data-i18n-key="Human resources">Human resources</span></span><span class="status status-late" data-i18n-key="Late · 9:12">Late · 9:12</span></div>
								</div>
							</section>
							<section class="panel">
								<div class="panel-head">
									<div>
										<div class="panel-title" data-i18n-key="Upcoming events">Upcoming events</div>
										<div class="panel-subtitle" data-i18n-key="Don&#x27;t miss what&#x27;s coming up">Don't miss what's coming up</div>
									</div><button class="text-link" data-module-link="calendar" data-i18n-key="Calendar">Calendar</button>
								</div>
								<div class="calendar-list">
									<div class="event-row"><span class="event-date"><strong>03</strong><span data-i18n-key="NOV">NOV</span></span><span><span class="event-name" data-i18n-key="Weekly team meeting">Weekly team meeting</span><span class="event-time" data-i18n-key="10:00 AM · Meeting room">10:00 AM · Meeting room</span></span></div>
									<div class="event-row"><span class="event-date"
											style="background:var(--green-soft);color:var(--green)"><strong>04</strong><span data-i18n-key="NOV">NOV</span></span><span><span class="event-name" data-i18n-key="Project review — Horizon Group">Project review — Horizon Group</span><span class="event-time" data-i18n-key="1:30 PM · Video call">1:30 PM · Video call</span></span></div>
									<div class="event-row"><span class="event-date"
											style="background:var(--orange-soft);color:var(--orange)"><strong>06</strong><span data-i18n-key="NOV">NOV</span></span><span><span class="event-name" data-i18n-key="Quarterly report deadline">Quarterly report deadline</span><span class="event-time" data-i18n-key="End of business day">End of business day</span></span></div>
								</div>
							</section>
							<section class="panel">
								<div class="panel-head">
									<div>
										<div class="panel-title" data-i18n-key="Recent activity">Recent activity</div>
										<div class="panel-subtitle" data-i18n-key="Latest updates from your team">Latest updates from your team</div>
									</div>
								</div>
								<div class="activity-list">
									<div class="activity-item"><span class="activity-icon"><svg viewBox="0 0 24 24" fill="none"
												stroke="currentColor" stroke-width="2">
												<path d="m5 12 4 4L19 6" />
											</svg></span><span><span class="activity-text"><b data-i18n-key="Ahmed Mansour">Ahmed Mansour</b> <span data-i18n-key="completed a task">completed a task</span> <b data-i18n-key="Update user interface">Update user interface</b></span><span class="activity-time" data-i18n-key="15 minutes ago">15 minutes ago</span></span></div>
									<div class="activity-item"><span class="activity-icon"
											style="background:var(--primary-soft);color:var(--primary)"><svg viewBox="0 0 24 24" fill="none"
												stroke="currentColor" stroke-width="1.7">
												<path d="M12 5v14m-7-7h14" />
											</svg></span><span><span class="activity-text"><b data-i18n-key="Reem Alqahtani">Reem Alqahtani</b> <span data-i18n-key="added a new client">added a new client</span> <b data-i18n-key="Nuqta Co.">Nuqta Co.</b></span><span class="activity-time" data-i18n-key="1 hour ago">1 hour ago</span></span></div>
								</div>
							</section>
						</div>
					</div>
				</section>
				<section id="modulePage" class="module-view" hidden></section>
			</div>
		</main>
	</div>
	<div class="toast" id="toast" role="status" aria-live="polite"></div>
	<script>
		(function () {
			var root = document.documentElement;
			var language = 'ar';
			var translations = {
				ar: @json(trans('ui', [], 'ar')),
				en: @json(trans('ui', [], 'en'))
			};
			var currentModule = 'overview';
			var userPermissions = JSON.parse(root.getAttribute('data-user-permissions') || '{}');
			var isAdmin = root.getAttribute('data-user-role') === 'admin';
			function hasPermission(module, action) {
				return isAdmin || (userPermissions[module] || []).indexOf(action) !== -1;
			}
			var moduleNames = {
				overview: ['Overview', 'A complete snapshot of your company and team.'],
				employees: ['Employees', 'Manage employees, departments, and your organizational structure.'],
				departments: ['Departments & structure', 'Organize departments, responsibilities, and managers.'],
				tasks: ['Task management', 'Create, assign, and track your team\'s work.'],
				projects: ['Project management', 'Track project progress, budgets, and clients.'],
				clients: ['Client management', 'Organize client records and manage business relationships.'],
				payments: ['Payments', 'Track payments, invoices, and cash flow.'],
				files: ['File management', 'Organize company files and share them with your team.'],
				reports: ['Reports & analytics', 'Turn business data into clear reports and insights.'],
				chat: ['Internal chat', 'Connect with colleagues and keep up with team updates.'],
				attendance: ['Time & attendance', 'Keep track of attendance records and team work hours.'],
				salary: ['Salary management', 'Manage payroll runs and monthly compensation.'],
				calendar: ['Calendar', 'Plan meetings, events, and task deadlines.'],
				inventory: ['Inventory', 'Track products, quantities, and stock movement.'],
				personal: ['My to-do list', 'Plan your daily priorities and track your progress.']
			};
			function startWorkspace() {
				var apiToken = document.querySelector('meta[name="csrf-token"]').content;
				var moduleRefreshTimer = null;
				var modulePath = {salary: 'payroll', personal: 'personal_tasks'};
				document.querySelectorAll('.nav-item[data-module]').forEach(function (item) {
					var module = modulePath[item.getAttribute('data-module')] || item.getAttribute('data-module');
					item.style.display = hasPermission(module, 'view') ? '' : 'none';
				});
				var fields = {
					employees: [['email','Email','email'],['phone','Phone','text'],['job_title','Job title','text'],['department_id','Department','departments']],
					departments: [['manager_id','Department manager','users'],['location','Location','text']],
					tasks: [['priority','Priority','priority'],['project_id','Related project','projects']],
					projects: [['client_id','Client','clients'],['start_date','Start date','date'],['end_date','Due date','date'],['progress','Progress %','number']],
					clients: [['company','Company','text'],['email','Email','email'],['phone','Phone','text'],['address','Address','text']],
					payments: [['invoice_number','Invoice number','text'],['payment_method','Payment method','text']],
					attendance: [['employee_id','Employee','employees']],
					payroll: [['employee_id','Employee','employees'],['period','Pay period','month'],['base_salary','Base salary','number'],['allowances','Allowances','number'],['deductions','Deductions','number']],
					calendar: [['location','Location or meeting link','text'],['attendees','Attendees','text']],
					inventory: [['sku','SKU','text'],['quantity','Quantity','number'],['unit','Unit','text'],['reorder_level','Reorder level','number'],['category','Category','text']],
					personal: [['priority','Priority','priority']],
					files: [], chat: []
				};
				var statusOptions = [
					['active','Active'],['in_progress','In progress'],['pending','Pending'],
					['overdue','Overdue'],['completed','Completed'],['paid','Paid'],['unpaid','Unpaid'],
					['draft','Draft'],['archived','Archived'],['checked_in','Checked in'],['checked_out','Checked out']
				];
				var dashboardLabels = {overview:'Overview', description:'Company data currently stored in your workspace.', employees:'Employees', projects:'Active projects', tasks:'Open tasks', revenue:'Payments this month', projectsTitle:'Recent projects', tasksTitle:'Recent tasks', attendanceTitle:'Time clock', clockIn:'Clock in', clockOut:'Clock out', reports:'Open reports', empty:'No records yet. Add your first record to get started.', loading:'Loading your data...', error:'Could not load dashboard data.'};
				var toastTimer;
				function notify(message) {
					var toast = document.getElementById('toast');
					toast.textContent = message;
					toast.classList.add('show');
					window.clearTimeout(toastTimer);
					toastTimer = window.setTimeout(function () { toast.classList.remove('show'); }, 3000);
				}
				function t(key) { return translations[language][key] || key; }
				function translateStaticText() {
					document.querySelectorAll('[data-i18n-key]').forEach(function (element) {
						var key = element.getAttribute('data-i18n-key');
						element.textContent = translations[language][key] || key;
					});
					document.querySelectorAll('[data-i18n-placeholder-key]').forEach(function (element) {
						var key = element.getAttribute('data-i18n-placeholder-key');
						element.placeholder = translations[language][key] || key;
					});
					var roleLabel = document.getElementById('userRoleLabel');
					if (roleLabel) {
						var roleKey = {admin:'Administrator', manager:'Manager', employee:'Employee'}[roleLabel.getAttribute('data-role-label')];
						roleLabel.textContent = translations[language][roleKey] || roleKey;
					}
				}
				function api(url, method, value, isForm) {
					var options = {method: method || 'GET', credentials: 'same-origin', headers: {'Accept': 'application/json'}};
					if (method && method !== 'GET') options.headers['X-CSRF-TOKEN'] = apiToken;
					if (value && isForm) options.body = value;
					else if (value) {
						options.headers['Content-Type'] = 'application/json';
						options.body = JSON.stringify(value);
					}
					return fetch(url, options).then(function (response) {
						return response.json().catch(function () { return {}; }).then(function (body) {
							if (!response.ok) {
								var messages = body.errors ? Object.keys(body.errors).map(function (key) { return body.errors[key][0]; }) : [];
								throw new Error(messages[0] || body.message || t('The request could not be completed.'));
							}
							return body;
						});
					});
				}
				function escapeText(value) {
					var element = document.createElement('span');
					element.textContent = value === null || value === undefined ? '' : String(value);
					return element.innerHTML;
				}
				function displayStatus(status) {
					var option = statusOptions.find(function (item) { return item[0] === status; });
					return option ? t(option[1]) : status;
				}
				function buildOverview() {
					var d = {};
					Object.keys(dashboardLabels).forEach(function (key) { d[key] = t(dashboardLabels[key]); });
					var financialCard = hasPermission('payments', 'view')
						? '<article class="stat-card"><div class="stat-label">' + d.revenue + '</div><div class="stat-value" id="dashboardRevenue">—</div></article>'
						: '';
					var employeeCard = hasPermission('employees', 'view')
						? '<article class="stat-card"><div class="stat-label">' + d.employees + '</div><div class="stat-value" id="dashboardEmployees">—</div></article>'
						: '';
					var projectCard = hasPermission('projects', 'view')
						? '<article class="stat-card"><div class="stat-label">' + d.projects + '</div><div class="stat-value" id="dashboardProjects">—</div></article>'
						: '';
					var taskCard = hasPermission('tasks', 'view')
						? '<article class="stat-card"><div class="stat-label">' + d.tasks + '</div><div class="stat-value" id="dashboardTasks">—</div></article>'
						: '';
					var projectPanel = hasPermission('projects', 'view')
						? '<section class="panel"><div class="panel-head"><div class="panel-title">' + d.projectsTitle + '</div><button class="text-link" data-open-module="projects">' + t('View projects') + '</button></div><div class="projects" id="dashboardProjectList"><div class="empty-state">' + d.loading + '</div></div></section>'
						: '';
					var taskPanel = hasPermission('tasks', 'view')
						? '<section class="panel"><div class="panel-head"><div class="panel-title">' + d.tasksTitle + '</div><button class="text-link" data-open-module="tasks">' + t('View tasks') + '</button></div><div class="task-list" id="dashboardTaskList"><div class="empty-state">' + d.loading + '</div></div></section>'
						: '';
					var attendancePanel = hasPermission('attendance', 'write')
						? '<section class="panel"><div class="panel-head"><div class="panel-title">' + d.attendanceTitle + '</div></div><p class="panel-subtitle">' + t('Record your daily working time.') + '</p><button class="primary-btn" id="attendanceClock" type="button" style="margin-top:14px">' + d.clockIn + '</button></section>'
						: '';
					var reportsCard = hasPermission('reports', 'view')
						? '<section class="panel"><div class="panel-head"><div class="panel-title">' + t('Reports') + '</div></div><p class="panel-subtitle">' + t('Summary of workspace records and financial totals.') + '</p><button class="text-link" data-open-module="reports">' + d.reports + ' ←</button></section>'
						: '';
					document.getElementById('overviewPage').innerHTML =
						'<div class="welcome-row"><div><h1 class="welcome-title">' + d.overview + '</h1><p class="welcome-copy">' + d.description + '</p></div>' + (hasPermission('tasks', 'write') ? '<button class="primary-btn" id="newTaskButton" type="button">＋ ' + t('New task') + '</button>' : '') + '</div>' +
						'<div class="stats-grid">' + employeeCard + projectCard + taskCard + financialCard + '</div>' +
						'<div class="dashboard-grid"><div class="column">' + projectPanel + taskPanel + '</div><div class="column">' + attendancePanel + reportsCard + '</div></div>';
					var newTaskButton = document.getElementById('newTaskButton');
					if (newTaskButton) newTaskButton.addEventListener('click', function () {
						document.querySelector('.nav-item[data-module="tasks"]').click();
						window.setTimeout(function () {
							var button = document.getElementById('createRecord');
							if (button) button.click();
						}, 0);
					});
					document.querySelectorAll('[data-open-module]').forEach(function (button) {
						button.addEventListener('click', function () {
							var target = button.getAttribute('data-open-module');
							if (target === 'reports') target = 'reports';
							document.querySelector('.nav-item[data-module="' + target + '"]').click();
						});
					});
					var dashboardClock = document.getElementById('attendanceClock');
					if (dashboardClock) dashboardClock.addEventListener('click', function () {
						var clock = document.getElementById('attendanceClock');
						var action = clock.getAttribute('data-action') || 'clock_in';
						api('/workspace/attendance/clock', 'POST', {employee_name: root.getAttribute('data-user-name'), action: action})
							.then(function (response) {
								var clockedIn = response.data.status === 'checked_in';
								clock.setAttribute('data-action', clockedIn ? 'clock_out' : 'clock_in');
								clock.textContent = clockedIn ? t(dashboardLabels.clockOut) : t(dashboardLabels.clockIn);
								notify(t(clockedIn ? 'Attendance recorded.' : 'Clock-out recorded.'));
								loadDashboard();
							})
							.catch(function (error) { notify(error.message); });
					});
					loadDashboard();
				}
				function loadDashboard() {
					api('/workspace/dashboard').then(function (data) {
						var employees = document.getElementById('dashboardEmployees');
						if (employees) employees.textContent = data.employees === null ? '' : data.employees;
						var projects = document.getElementById('dashboardProjects');
						if (projects) projects.textContent = data.active_projects === null ? '' : data.active_projects;
						var tasks = document.getElementById('dashboardTasks');
						if (tasks) tasks.textContent = data.open_tasks === null ? '' : data.open_tasks;
						var revenue = document.getElementById('dashboardRevenue');
						if (revenue && data.revenue !== null) revenue.textContent = Number(data.revenue || 0).toLocaleString(language === 'ar' ? 'ar-EG' : 'en-US') + ' ' + t('LE');
						var taskBadge = document.getElementById('taskBadge');
						if (taskBadge) taskBadge.textContent = data.open_tasks === null ? '' : data.open_tasks;
						var clock = document.getElementById('attendanceClock');
						if (clock) {
							clock.setAttribute('data-action', data.clocked_in ? 'clock_out' : 'clock_in');
							clock.textContent = data.clocked_in ? t(dashboardLabels.clockOut) : t(dashboardLabels.clockIn);
						}
						var projectList = document.getElementById('dashboardProjectList');
						if (projectList) projectList.textContent = '';
						if (projectList && !data.projects.length) projectList.innerHTML = '<div class="empty-state">' + t(dashboardLabels.empty) + '</div>';
						data.projects.forEach(function (record) {
							if (!projectList) return;
							var item = document.createElement('div');
							item.className = 'project-row';
							var name = document.createElement('div');
							name.className = 'project-name';
							name.textContent = record.title;
							var track = document.createElement('div');
							track.className = 'progress-track';
							var fill = document.createElement('div');
							fill.className = 'progress-fill';
							var progress = Math.max(0, Math.min(100, Number(record.metadata && record.metadata.progress || 0)));
							fill.style.width = progress + '%';
							track.appendChild(fill);
							var percent = document.createElement('div');
							percent.className = 'project-progress';
							percent.textContent = progress + '%';
							item.appendChild(name);
							item.appendChild(track);
							item.appendChild(percent);
							projectList.appendChild(item);
						});
						var taskList = document.getElementById('dashboardTaskList');
						if (taskList) taskList.textContent = '';
						if (taskList && !data.tasks.length) taskList.innerHTML = '<div class="empty-state">' + t(dashboardLabels.empty) + '</div>';
						data.tasks.forEach(function (record) {
							if (!taskList) return;
							var row = document.createElement('div');
							row.className = 'task-row';
							var info = document.createElement('div');
							info.className = 'task-info';
							var title = document.createElement('div');
							title.className = 'task-name';
							title.textContent = record.title;
							var description = document.createElement('div');
							description.className = 'task-meta';
							description.textContent = record.description || displayStatus(record.status);
							info.appendChild(title);
							info.appendChild(description);
							var status = document.createElement('span');
							status.className = 'task-tag ' + (record.status === 'completed' ? 'tag-low' : 'tag-mid');
							status.textContent = displayStatus(record.status);
							row.appendChild(info);
							row.appendChild(status);
							taskList.appendChild(row);
						});
					}).catch(function (error) { notify(error.message || t(dashboardLabels.error)); });
				}
				function formField(definition, record, related) {
					var key = definition[0];
					var type = definition[2];
					var wrapper = document.createElement('label');
					wrapper.className = 'form-field' + (type === 'textarea' ? ' full' : '');
					var caption = document.createElement('span');
					caption.textContent = t(definition[1]);
					wrapper.appendChild(caption);
					var input;
					if (type === 'priority' || type === 'status' || type === 'projects' || type === 'clients' || type === 'employees' || type === 'departments' || type === 'users' || type === 'role' || type === 'account_status') {
						input = document.createElement('select');
						if (['projects','clients','employees','departments','users'].indexOf(type) !== -1) {
							var emptyOption = document.createElement('option');
							emptyOption.value = '';
							emptyOption.textContent = t('Unassigned');
							input.appendChild(emptyOption);
						}
						var options = type === 'priority'
							? [['low','Low'],['medium','Medium'],['high','High']]
							: type === 'status' ? statusOptions
							: type === 'role' ? [['employee','Employee'],['manager','Manager']]
							: type === 'account_status' ? [['1','Active'],['0','Inactive']]
							: type === 'employees' ? (related.employees || []).map(function (item) { return [String(item.id), item.title]; })
							: type === 'departments' ? (related.departments || []).map(function (item) { return [String(item.id), item.title]; })
							: type === 'users' ? (related.users || []).map(function (item) { return [String(item.id), item.name]; })
							: (related[type] || []).map(function (item) { return [String(item.id), item.title]; });
						options.forEach(function (option) {
							var choice = document.createElement('option');
							choice.value = option[0];
							choice.textContent = t(option[1]);
							input.appendChild(choice);
						});
					} else if (type === 'textarea') {
						input = document.createElement('textarea');
					} else {
						input = document.createElement('input');
						input.type = type;
						if (type === 'number') {
							input.step = ['quantity','reorder_level'].indexOf(key) !== -1 ? '1' : 'any';
							input.min = '0';
						}
					}
					input.name = key;
					input.value = record && record.metadata && record.metadata[key] ? record.metadata[key] : '';
					wrapper.appendChild(input);
					return wrapper;
				}
				function renderReports(page) {
					api('/workspace/reports').then(function (response) {
						var names = {employees:'Employees',departments:'Departments',tasks:'Tasks',projects:'Projects',clients:'Clients',payments:'Payments',files:'Files',chat:'Internal chat',attendance:'Attendance',payroll:'Payroll',calendar:'Calendar events',inventory:'Inventory',personal_tasks:'Personal tasks'};
						page.innerHTML = '<div class="welcome-row"><div><h1 class="welcome-title">' + t('Reports & analytics') + '</h1><p class="welcome-copy">' + t('Live summary of tasks, payments, payroll, attendance, and inventory.') + '</p></div></div><div class="stats-grid" id="reportSummary"></div><section class="panel"><div class="panel-head"><div class="panel-title">' + t('Records by module') + '</div></div><div id="reportRows"></div></section>';
						var summaryItems = [
							['payments_received','Payments received',true],
							['payments_outstanding','Payments outstanding',true],
							['payroll_paid','Payroll paid',true],
							['payroll_outstanding','Payroll outstanding',true],
							['tasks_open','Open tasks',false],
							['tasks_completed','Completed tasks',false],
							['attendance_today','Attendance today',false],
							['low_stock','Items at reorder level',false]
						];
						var summaryGrid = page.querySelector('#reportSummary');
						summaryItems.forEach(function (item) {
							if (response.summary[item[0]] === null) return;
							var card = document.createElement('article');
							card.className = 'stat-card';
							var name = document.createElement('div');
							name.className = 'stat-label';
							name.textContent = t(item[1]);
							var value = document.createElement('div');
							value.className = 'stat-value';
							var amount = Number(response.summary[item[0]] || 0).toLocaleString(language === 'ar' ? 'ar-EG' : 'en-US');
							value.textContent = amount + (item[2] ? ' ' + t('LE') : '');
							card.appendChild(name);
							card.appendChild(value);
							summaryGrid.appendChild(card);
						});
						var target = page.querySelector('#reportRows');
						response.data.forEach(function (item) {
							var row = document.createElement('div');
							row.className = 'report-row';
							var moduleLabel = names[item.module] ? t(names[item.module]) : item.module;
							var count = document.createElement('strong');
							count.textContent = moduleLabel;
							var total = document.createElement('span');
							total.textContent = Number(item.total).toLocaleString(language === 'ar' ? 'ar-EG' : 'en-US') + ' · ' + Number(item.amount || 0).toLocaleString(language === 'ar' ? 'ar-EG' : 'en-US') + ' ' + t('LE');
							row.appendChild(count);
							row.appendChild(total);
							target.appendChild(row);
						});
						if (!response.data.length) target.innerHTML = '<div class="empty-state">' + t('There are no records to report yet.') + '</div>';
					}).catch(function (error) { page.innerHTML = '<div class="panel empty-state">' + escapeText(error.message) + '</div>'; });
				}
				function renderModule(key) {
					window.clearInterval(moduleRefreshTimer);
					moduleRefreshTimer = null;
					currentModule = key;
					var overview = document.getElementById('overviewPage');
					var page = document.getElementById('modulePage');
					overview.hidden = key !== 'overview';
					page.hidden = key === 'overview';
					if (key === 'overview') {
						buildOverview();
						return;
					}
					if (key === 'reports') {
						renderReports(page);
						return;
					}
					var details = moduleNames[key];
					var title = t(details[0]);
					var description = t(details[1]);
					var canManage = isAdmin;
					var permissionModule = modulePath[key] || key;
					var canCreateRecord = hasPermission(permissionModule, 'write') && key !== 'attendance';
					var relatedData = {};
					var userOptions = [];
					var currentPage = 1;
					page.innerHTML = '<div class="welcome-row"><div><h1 class="welcome-title">' + title + '</h1><p class="welcome-copy">' + description + '</p></div><div style="display:flex;gap:8px">' + (canCreateRecord ? '<button class="primary-btn" id="createRecord" type="button">＋ ' + t('Add record') + '</button>' : '') + (key === 'employees' && canManage ? '<button class="secondary-btn" id="createEmployeeAccount" type="button">' + t('Create employee account') + '</button>' : '') + '</div></div><section class="panel" id="recordFormPanel" hidden><div class="panel-head"><div class="panel-title" id="recordFormTitle">' + t('New record') + '</div></div><form class="record-form" id="recordForm"></form></section><section class="panel" id="recordsPanel"><div class="panel-head"><div><div class="panel-title">' + title + '</div><div class="panel-subtitle" id="recordCount">' + t('Loading records...') + '</div></div></div><div class="module-toolbar"><input type="search" id="moduleSearch" placeholder="' + t('Search records...') + '"><select id="statusFilter"><option value="">' + t('All statuses') + '</option>' + statusOptions.map(function (item) { return '<option value="' + item[0] + '">' + t(item[1]) + '</option>'; }).join('') + '</select></div><div class="record-list" id="recordList"></div><div class="pagination" id="pagination"></div></section>';
					var attendanceClockButton = null;
					if (key === 'attendance') {
						attendanceClockButton = document.createElement('button');
						attendanceClockButton.type = 'button';
						attendanceClockButton.className = 'secondary-btn';
						attendanceClockButton.id = 'moduleAttendanceClock';
						attendanceClockButton.disabled = true;
						attendanceClockButton.textContent = t('Loading attendance status...');
						page.querySelector('.welcome-row > div:last-child').appendChild(attendanceClockButton);
						attendanceClockButton.addEventListener('click', function () {
							var action = attendanceClockButton.getAttribute('data-action') || 'clock_in';
							attendanceClockButton.disabled = true;
							api('/workspace/attendance/clock', 'POST', {action: action}).then(function (result) {
								var clockedIn = result.data.status === 'checked_in';
								attendanceClockButton.setAttribute('data-action', clockedIn ? 'clock_out' : 'clock_in');
								attendanceClockButton.textContent = clockedIn ? t('Clock out') : t('Clock in');
								attendanceClockButton.disabled = false;
								notify(t(clockedIn ? 'Attendance recorded.' : 'Clock-out recorded.'));
								loadRecords();
								loadDashboard();
							}).catch(function (error) {
								attendanceClockButton.disabled = false;
								notify(error.message);
							});
						});
					}
					var createRecordButton = page.querySelector('#createRecord');
					if (createRecordButton) {
						createRecordButton.disabled = true;
						createRecordButton.addEventListener('click', function () { showForm(null, relatedData, userOptions); });
					}
					var employeeAccountButton = page.querySelector('#createEmployeeAccount');
					if (employeeAccountButton) {
						employeeAccountButton.disabled = true;
						employeeAccountButton.addEventListener('click', function () { showEmployeeAccountForm(); });
					}
					page.querySelector('#moduleSearch').addEventListener('input', function () { currentPage = 1; loadRecords(); });
					page.querySelector('#statusFilter').addEventListener('change', function () { currentPage = 1; loadRecords(); });
					loadRecords();
					if (key === 'chat') moduleRefreshTimer = window.setInterval(loadRecords, 5000);
					function showEmployeeAccountForm(record) {
						var panel = page.querySelector('#recordFormPanel');
						var form = page.querySelector('#recordForm');
						var editing = Boolean(record);
						var metadata = record ? record.metadata || {} : {};
						var accountValues = {
							name: record ? record.title : '',
							email: metadata.email || '',
							role: metadata.role || 'employee',
							is_active: metadata.is_active ? '1' : '0',
							job_title: metadata.job_title || '',
							department_id: metadata.department_id || '',
							phone: metadata.phone || '',
							permissions: metadata.permissions || JSON.parse(root.getAttribute('data-default-permissions') || '{}')
						};
						page.querySelector('#recordFormTitle').textContent = t(editing ? 'Edit employee account' : 'Create employee account');
						panel.hidden = false;
						form.innerHTML = '';
						var accountFields = [['name','Employee name','text'],['email','Email address','email'],['password',editing ? 'New password (optional)' : 'Temporary password (at least 10 characters)','password'],['role','Account role','role']];
						if (editing) accountFields.push(['is_active','Account status','account_status']);
						accountFields = accountFields.concat([['job_title','Job title','text'],['department_id','Department','departments'],['phone','Phone','text']]);
						accountFields.forEach(function (item) {
							var field = formField(item, {metadata: accountValues}, relatedData);
							if (item[0] === 'password' && !editing) field.querySelector('input').required = true;
							form.appendChild(field);
						});
						var permissionSection = document.createElement('fieldset');
						permissionSection.className = 'form-field full permission-section';
						permissionSection.innerHTML = '<legend>' + t('Module permissions') + '</legend>';
						var permissionGrid = document.createElement('div');
						permissionGrid.className = 'permission-grid';
						var permissionModules = [
							['employees','Employees'],['departments','Departments'],['tasks','Tasks'],
							['projects','Projects'],['clients','Clients'],['payments','Payments'],
							['files','Files'],['reports','Reports'],['chat','Internal chat'],
							['attendance','Attendance'],['payroll','Payroll'],['calendar','Calendar'],
							['inventory','Inventory'],['personal_tasks','Personal tasks']
						];
						var permissionActions = [['view','View'],['write','Create and edit'],['delete','Delete']];
						permissionModules.forEach(function (module) {
							var row = document.createElement('div');
							row.className = 'permission-row';
							var title = document.createElement('strong');
							title.textContent = t(module[1]);
							row.appendChild(title);
							permissionActions.forEach(function (action) {
								var checkbox = document.createElement('input');
								checkbox.type = 'checkbox';
								checkbox.value = action[0];
								checkbox.dataset.permissionModule = module[0];
								checkbox.dataset.permissionAction = action[0];
								checkbox.checked = (accountValues.permissions[module[0]] || []).indexOf(action[0]) !== -1;
								checkbox.disabled = module[0] === 'reports' && action[0] !== 'view';
								var caption = document.createElement('label');
								caption.appendChild(checkbox);
								caption.appendChild(document.createTextNode(t(action[1])));
								row.appendChild(caption);
							});
							permissionGrid.appendChild(row);
						});
						permissionSection.appendChild(permissionGrid);
						form.appendChild(permissionSection);
						var actions = document.createElement('div');
						actions.className = 'form-actions';
						actions.innerHTML = '<button class="secondary-btn" type="button">' + t('Cancel') + '</button><button class="primary-btn" type="submit">' + t(editing ? 'Save account' : 'Create account') + '</button>';
						form.appendChild(actions);
						actions.querySelector('button').addEventListener('click', function () { panel.hidden = true; });
						form.onsubmit = function (event) {
							event.preventDefault();
							var values = {};
							new FormData(form).forEach(function (value, key) { values[key] = value; });
							values.permissions = {};
							permissionModules.forEach(function (module) { values.permissions[module[0]] = []; });
							form.querySelectorAll('[data-permission-module]:checked').forEach(function (checkbox) {
								values.permissions[checkbox.dataset.permissionModule].push(checkbox.dataset.permissionAction);
							});
							values.password = values.password || null;
							if (editing) values.is_active = values.is_active === '1';
							var url = editing ? '/workspace/employees/' + record.id + '/account' : '/workspace/employees/account';
							api(url, editing ? 'PUT' : 'POST', values).then(function () { panel.hidden = true; notify(t(editing ? 'Employee account updated.' : 'Employee account created.')); loadRecords(); loadDashboard(); }).catch(function (error) { notify(error.message); });
						};
					}
					function showForm(record, related, users) {
						var panel = page.querySelector('#recordFormPanel');
						var form = page.querySelector('#recordForm');
						var current = record || {};
						var formKey = key === 'salary' ? 'payroll' : key;
						page.querySelector('#recordFormTitle').textContent = record ? t('Edit record') : t('New record');
						panel.hidden = false;
						form.innerHTML = '';
						form.appendChild(formField(['title','Title','text'], {metadata:{}}, related));
						form.querySelector('[name="title"]').value = current.title || '';
						form.appendChild(formField(['description','Description','textarea'], {metadata:{}}, related));
						form.querySelector('[name="description"]').value = current.description || '';
						var statusField = formField(['status','Status','status'], current, related);
						statusField.querySelector('select').value = current.status || (formKey === 'tasks' ? 'pending' : ['payments','payroll'].indexOf(formKey) !== -1 ? 'unpaid' : 'active');
						form.appendChild(statusField);
						if (['projects','payments','inventory'].indexOf(formKey) !== -1) {
							var amountField = formField(['amount','Amount','number'], {metadata:{}}, related);
							amountField.querySelector('input').name = 'amount';
							amountField.querySelector('input').value = current.amount || '';
							form.appendChild(amountField);
						}
						if (['tasks','projects','payments','calendar','payroll','attendance'].indexOf(formKey) !== -1) {
							var dateField = formField(['record_date','Date','date'], {metadata:{}}, related);
							dateField.querySelector('input').name = 'record_date';
							dateField.querySelector('input').value = current.record_date ? String(current.record_date).slice(0,10) : ['payments','payroll','attendance'].indexOf(formKey) !== -1 ? root.getAttribute('data-today') : '';
							form.appendChild(dateField);
						}
						(fields[formKey] || []).forEach(function (definition) {
							var field = formField(definition, current, related);
							if (definition[0] === 'priority' && current.metadata && current.metadata.priority) field.querySelector('select').value = current.metadata.priority;
							if (formKey === 'inventory' && record && definition[0] === 'quantity') {
								field.querySelector('input').readOnly = true;
								field.querySelector('span').textContent += t(' (change with Adjust stock)');
							}
							form.appendChild(field);
						});
						if (formKey === 'payroll') {
							var netSalary = document.createElement('div');
							netSalary.className = 'form-field full';
							netSalary.innerHTML = '<span>' + t('Calculated net salary') + '</span><strong id="payrollNetAmount"></strong>';
							form.appendChild(netSalary);
							var updateNetSalary = function () {
								var base = Number(form.querySelector('[name="base_salary"]').value || 0);
								var allowances = Number(form.querySelector('[name="allowances"]').value || 0);
								var deductions = Number(form.querySelector('[name="deductions"]').value || 0);
								var total = Math.max(0, base + allowances - deductions);
								form.querySelector('#payrollNetAmount').textContent = total.toLocaleString(language === 'ar' ? 'ar-EG' : 'en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' ' + t('LE');
							};
							['base_salary','allowances','deductions'].forEach(function (name) {
								form.querySelector('[name="' + name + '"]').addEventListener('input', updateNetSalary);
							});
							updateNetSalary();
						}
						if (['tasks','projects'].indexOf(key) !== -1 && users.length) {
							var assigned = document.createElement('label');
							assigned.className = 'form-field';
							var caption = document.createElement('span');
							caption.textContent = t('Assigned to');
							var select = document.createElement('select');
							select.name = 'assigned_user_id';
							select.innerHTML = '<option value="">' + t('Unassigned') + '</option>';
							users.forEach(function (user) {
								var option = document.createElement('option');
								option.value = user.id;
								option.textContent = user.name;
								select.appendChild(option);
							});
							if (current.assigned_user_id) select.value = current.assigned_user_id;
							assigned.appendChild(caption);
							assigned.appendChild(select);
							form.appendChild(assigned);
						}
						if (key === 'files' && !record) {
							var fileField = document.createElement('label');
							fileField.className = 'form-field full';
							fileField.innerHTML = '<span>' + t('Choose a file (up to 10 MB)') + '</span><input type="file" name="file" required>';
							form.appendChild(fileField);
						}
						if (key === 'files' && !record) {
							var fileDescription = document.createElement('label');
							fileDescription.className = 'form-field full';
							fileDescription.innerHTML = '<span>' + t('Description') + '</span><input name="file_description" maxlength="2000">';
							form.appendChild(fileDescription);
						}
						var actions = document.createElement('div');
						actions.className = 'form-actions';
						actions.innerHTML = '<button class="secondary-btn" type="button">' + t('Cancel') + '</button><button class="primary-btn" type="submit">' + t(record ? 'Save changes' : 'Save record') + '</button>';
						form.appendChild(actions);
						actions.querySelector('button').addEventListener('click', function () { panel.hidden = true; });
						form.onsubmit = function (event) {
							event.preventDefault();
							if (key === 'files') {
								var upload = new FormData();
								upload.append('file', form.querySelector('[name="file"]').files[0]);
								upload.append('description', form.querySelector('[name="file_description"]').value);
								api('/workspace/files/upload', 'POST', upload, true).then(function () { panel.hidden = true; notify(t('File uploaded securely.')); loadRecords(); }).catch(function (error) { notify(error.message); });
								return;
							}
							var data = new FormData(form);
							var values = {title:data.get('title'),description:data.get('description'),status:data.get('status'),amount:data.get('amount') || null,record_date:data.get('record_date') || null,assigned_user_id:data.get('assigned_user_id') || null,metadata:{}};
							(fields[formKey] || []).forEach(function (definition) { if (data.get(definition[0])) values.metadata[definition[0]] = data.get(definition[0]); });
							var url = '/workspace/' + (modulePath[key] || key) + (record ? '/' + record.id : '');
							api(url, record ? 'PUT' : 'POST', values).then(function () { panel.hidden = true; notify(t('Record saved.')); loadRecords(); loadDashboard(); }).catch(function (error) { notify(error.message); });
						};
					}
					function loadRecords() {
						var params = new URLSearchParams();
						var search = page.querySelector('#moduleSearch').value.trim();
						var status = page.querySelector('#statusFilter').value;
						params.set('page', currentPage);
						if (search) params.set('search', search);
						if (status) params.set('status', status);
						var url = '/workspace/' + (modulePath[key] || key) + '?' + params.toString();
						api(url).then(function (response) {
							var items = response.data.data;
							relatedData = response.related || {};
							relatedData.users = response.users || [];
							userOptions = response.users || [];
							if (attendanceClockButton) {
								var clockedIn = Boolean(response.clocked_in);
								attendanceClockButton.setAttribute('data-action', clockedIn ? 'clock_out' : 'clock_in');
								attendanceClockButton.textContent = clockedIn ? t('Clock out') : t('Clock in');
								attendanceClockButton.disabled = false;
							}
							if (createRecordButton) createRecordButton.disabled = false;
							if (employeeAccountButton) employeeAccountButton.disabled = false;
							page.querySelector('#recordCount').textContent = t('Records: ') + response.data.total;
							var pagination = page.querySelector('#pagination');
							pagination.textContent = '';
							if (response.data.last_page > 1) {
								var previous = document.createElement('button');
								previous.type = 'button';
								previous.textContent = t('Previous');
								previous.disabled = response.data.current_page <= 1;
								previous.addEventListener('click', function () { currentPage--; loadRecords(); });
								var pageLabel = document.createElement('span');
								pageLabel.textContent = response.data.current_page + ' / ' + response.data.last_page;
								var next = document.createElement('button');
								next.type = 'button';
								next.textContent = t('Next');
								next.disabled = response.data.current_page >= response.data.last_page;
								next.addEventListener('click', function () { currentPage++; loadRecords(); });
								pagination.appendChild(previous);
								pagination.appendChild(pageLabel);
								pagination.appendChild(next);
							}
							var list = page.querySelector('#recordList');
							list.textContent = '';
							if (!items.length) {
								list.innerHTML = '<div class="empty-state">' + t('No records here yet. Use “Add record” to get started.') + '</div>';
								return;
							}
							items.forEach(function (record) {
								var card = document.createElement('article');
								card.className = 'record-card';
								var ownsRecord = Number(record.created_by) === Number(root.getAttribute('data-user-id'));
								var linkedEmployee = key === 'employees' && record.metadata && record.metadata.user_id;
								var canEditRecord = hasPermission(permissionModule, 'write') && (key !== 'chat' || canManage || ownsRecord) && key !== 'attendance' && !linkedEmployee;
								var canDeleteRecord = hasPermission(permissionModule, 'delete') && (canManage || ownsRecord) && !linkedEmployee;
								var content = document.createElement('div');
								var title = document.createElement('div');
								title.className = 'record-title';
								title.textContent = record.title;
								var status = document.createElement('span');
								status.className = 'record-status';
								status.textContent = displayStatus(record.status);
								content.appendChild(title);
								content.appendChild(status);
								if (record.description) {
									var description = document.createElement('div');
									description.className = 'record-description';
									description.textContent = record.description;
									content.appendChild(description);
								}
								var summary = [];
								if (record.amount !== null && record.amount !== undefined) summary.push(Number(record.amount).toLocaleString(language === 'ar' ? 'ar-EG' : 'en-US') + ' ' + t('LE'));
								if (record.record_date) summary.push(String(record.record_date).slice(0,10));
								if (record.assignee) summary.push(t('Assigned: ') + record.assignee.name);
								if (key === 'chat' && record.creator) summary.push(t('From: ') + record.creator.name);
								if (key === 'inventory' && record.metadata && record.metadata.movements && record.metadata.movements.length) {
									var latestMovement = record.metadata.movements[record.metadata.movements.length - 1];
									summary.push(t('Last stock movement: ') + (latestMovement.change > 0 ? '+' : '') + latestMovement.change + (latestMovement.reason ? ' · ' + latestMovement.reason : ''));
								}
								(fields[key === 'salary' ? 'payroll' : key] || []).forEach(function (definition) {
									var value = record.metadata && record.metadata[definition[0]];
									if (value && definition[0] !== 'password') {
										if (['projects','clients','employees','departments','users'].indexOf(definition[2]) !== -1) {
											var relatedRecord = (relatedData[definition[2]] || []).find(function (item) { return String(item.id) === String(value); });
											if (relatedRecord) value = relatedRecord.title || relatedRecord.name;
										}
										summary.push(t(definition[1] + ': ') + value);
									}
								});
								if (summary.length) {
									var meta = document.createElement('div');
									meta.className = 'record-meta';
									meta.textContent = summary.join(' · ');
									content.appendChild(meta);
								}
								var actions = document.createElement('div');
								actions.className = 'record-actions';
								if (key === 'inventory' && hasPermission('inventory', 'write')) {
									var adjust = document.createElement('button');
									adjust.type = 'button';
									adjust.textContent = t('Adjust stock');
									adjust.addEventListener('click', function () { showInventoryAdjustment(record); });
									actions.appendChild(adjust);
								}
								if (key === 'employees' && canManage && linkedEmployee) {
									var manageAccount = document.createElement('button');
									manageAccount.type = 'button';
									manageAccount.textContent = t('Manage account');
									manageAccount.addEventListener('click', function () { showEmployeeAccountForm(record); });
									actions.appendChild(manageAccount);
								}
								if (key === 'files') {
									var download = document.createElement('a');
									download.href = '/workspace/files/' + record.id + '/download';
									download.textContent = t('Download');
									actions.appendChild(download);
								} else if (canEditRecord) {
									var edit = document.createElement('button');
									edit.type = 'button';
									edit.textContent = t('Edit');
									edit.addEventListener('click', function () { showForm(record, response.related || {}, response.users || []); });
									actions.appendChild(edit);
								}
								if (canDeleteRecord) {
									var remove = document.createElement('button');
									remove.className = 'danger';
									remove.type = 'button';
									remove.textContent = t('Delete');
									remove.addEventListener('click', function () {
										if (!window.confirm(t('Permanently delete this record?'))) return;
										api('/workspace/' + (modulePath[key] || key) + '/' + record.id, 'DELETE').then(function () { notify(t('Record deleted.')); loadRecords(); loadDashboard(); }).catch(function (error) { notify(error.message); });
									});
									actions.appendChild(remove);
								}
								card.appendChild(content);
								if (actions.children.length) card.appendChild(actions);
								list.appendChild(card);
							});
						}).catch(function (error) { notify(error.message); });
					}
					function showInventoryAdjustment(record) {
						var panel = page.querySelector('#recordFormPanel');
						var form = page.querySelector('#recordForm');
						page.querySelector('#recordFormTitle').textContent = t('Adjust stock: ') + record.title;
						panel.hidden = false;
						form.innerHTML = '';
						var actionField = document.createElement('label');
						actionField.className = 'form-field';
						actionField.innerHTML = '<span>' + t('Movement type') + '</span><select name="action"><option value="increase">' + t('Stock in') + '</option><option value="decrease">' + t('Stock out') + '</option></select>';
						var quantityField = document.createElement('label');
						quantityField.className = 'form-field';
						quantityField.innerHTML = '<span>' + t('Quantity') + '</span><input type="number" name="quantity" min="1" step="1" required>';
						var reasonField = document.createElement('label');
						reasonField.className = 'form-field full';
						reasonField.innerHTML = '<span>' + t('Reason (optional)') + '</span><input name="reason" maxlength="500">';
						form.appendChild(actionField);
						form.appendChild(quantityField);
						form.appendChild(reasonField);
						var actions = document.createElement('div');
						actions.className = 'form-actions';
						actions.innerHTML = '<button class="secondary-btn" type="button">' + t('Cancel') + '</button><button class="primary-btn" type="submit">' + t('Save movement') + '</button>';
						form.appendChild(actions);
						actions.querySelector('button').addEventListener('click', function () { panel.hidden = true; });
						form.onsubmit = function (event) {
							event.preventDefault();
							var values = {};
							new FormData(form).forEach(function (value, key) { values[key] = value; });
							api('/workspace/inventory/' + record.id + '/adjust', 'POST', values).then(function () {
								panel.hidden = true;
								notify(t('Stock updated and movement recorded.'));
								loadRecords();
							}).catch(function (error) { notify(error.message); });
						};
					}
				}
				function setLanguage(nextLanguage) {
					language = nextLanguage;
					root.lang = language;
					root.dir = language === 'ar' ? 'rtl' : 'ltr';
					document.title = translations[language]['Talal | Business management'];
					translateStaticText();
					document.querySelector('.brand').setAttribute('aria-label', t('Talal'));
					document.getElementById('languageToggle').textContent = language === 'ar' ? 'EN' : 'ع';
					document.getElementById('searchInput').dir = language === 'ar' ? 'rtl' : 'ltr';
					document.getElementById('themeToggle').setAttribute('aria-label', t('Toggle theme'));
					document.getElementById('themeToggle').title = t('Toggle theme');
					document.getElementById('notificationButton').setAttribute('aria-label', t('Notifications'));
					var navigation = document.querySelectorAll('.sidebar nav[aria-label]');
					if (navigation[0]) navigation[0].setAttribute('aria-label', t('Main menu'));
					if (navigation[1]) navigation[1].setAttribute('aria-label', t('Workspace'));
					var today = new Date();
					document.getElementById('pageGreeting').textContent = t('Good day, ') + root.getAttribute('data-user-name') + ' 👋';
					document.getElementById('pageDate').textContent = new Intl.DateTimeFormat(language === 'ar' ? 'ar-SA' : 'en-US', {weekday:'long',year:'numeric',month:'long',day:'numeric'}).format(today);
					renderModule(currentModule);
				}
				document.getElementById('languageToggle').addEventListener('click', function () { setLanguage(language === 'ar' ? 'en' : 'ar'); });
				document.getElementById('themeToggle').addEventListener('click', function () {
					var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
					root.setAttribute('data-theme', next);
					try { window.localStorage.setItem('nawaat-theme', next); } catch (error) { notify(t('Unable to save the theme preference.')); }
				});
				document.getElementById('notificationButton').addEventListener('click', function () { notify(t('You have no new notifications.')); });
				document.getElementById('searchInput').addEventListener('input', function () {
					if (currentModule !== 'overview' && currentModule !== 'reports') {
						var search = document.getElementById('moduleSearch');
						if (search) { search.value = document.getElementById('searchInput').value; search.dispatchEvent(new Event('input')); }
					}
				});
				document.querySelectorAll('.nav-item').forEach(function (button) {
					button.addEventListener('click', function () {
						document.querySelectorAll('.nav-item').forEach(function (item) { item.classList.remove('active'); });
						button.classList.add('active');
						renderModule(button.getAttribute('data-module'));
						document.getElementById('sidebar').classList.remove('open');
						document.getElementById('overlay').classList.remove('show');
					});
				});
				document.querySelectorAll('[data-module-link]').forEach(function (button) {
					button.addEventListener('click', function () {
						var target = button.getAttribute('data-module-link');
						var nav = document.querySelector('.nav-item[data-module="' + target + '"]');
						if (nav) nav.click();
					});
				});
				var sidebar = document.getElementById('sidebar');
				var overlay = document.getElementById('overlay');
				document.getElementById('menuToggle').addEventListener('click', function () { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
				overlay.addEventListener('click', function () { sidebar.classList.remove('open'); overlay.classList.remove('show'); });
				try {
					var theme = window.localStorage.getItem('nawaat-theme');
					if (theme === 'dark' || theme === 'light') root.setAttribute('data-theme', theme);
				} catch (error) { notify(t('Unable to restore the saved theme.')); }
				setLanguage('ar');
			}
			startWorkspace();
			return;
		}());
</script>
</body>

</html>