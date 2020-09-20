import React from 'react';
import ReactDOM from 'react-dom';
import Reports from './Reports';
import registerServiceWorker from './registerServiceWorker';

ReactDOM.render(<Reports />, document.getElementById('wplms_reports'));
registerServiceWorker();
