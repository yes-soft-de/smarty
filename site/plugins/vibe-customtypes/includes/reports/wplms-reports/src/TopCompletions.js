import React, { Component } from 'react';
import {Bar} from 'react-chartjs-2';


class TopCompletions extends Component {

	constructor(props){
		super(props);

		this.state={
			labels:[],
			data:[]
		}

	}

	componentWillMount(){

		let API_URL = (window.wplms_reports_settings)?window.wplms_reports_settings.settings.api_url:'http://localhost/wordpress/wp-json/wplms/v1/reports';
		fetch(API_URL+'/top_learner_completions?\
			user_id='+this.props.user.user_id+'&role='+this.props.user.role+'&security='+this.props.user.security+'&number=10')
		.then(response => response.json())
      	.then(json => {

      		console.log('Fetched Data');
      		console.log(json);
      		if(json.data.length){
      			this.setState({data:json.data});
      			this.setState({labels:json.label});
      		}
      	});
	}
    render() {

    	let data = {
				labels: this.state.labels,
				datasets: [{
					data: this.state.data,
					label: (window.wplms_reports_settings)?window.wplms_reports_settings.translations.top_completions_title:'Top Completions',
			      	backgroundColor: 'rgba(255,99,132,0.2)',
			      	borderColor: 'rgba(255,99,132,1)',
			      	borderWidth: 1,
			      	hoverBackgroundColor: 'rgba(255,99,132,0.4)',
			      	hoverBorderColor: 'rgba(255,99,132,1)',
				}]
			};
        return (
            <div className="top_courses report_block">
              	<h3>{(window.wplms_reports_settings)?window.wplms_reports_settings.translations.top_completions_title:'Top learners'}</h3>
              	{
              		(this.state.labels.count)?
              		<Bar
			          data={data}
			          width={100}
			          height={50}
			          options={{
			            maintainAspectRatio: false
			          }}
			        />
			        :'N.A'
              	}
              	
            </div>
        );
    }
}

export default TopCompletions;