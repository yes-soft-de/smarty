import React, { Component } from 'react';
import {Doughnut} from 'react-chartjs-2';


class TopCourses extends Component {

	constructor(props){
		super(props);

		this.state={
			labels:[],
			data:[]
		}

	}

	componentWillMount(){
		let API_URL = (window.wplms_reports_settings)?window.wplms_reports_settings.settings.api_url:'http://localhost/wordpress/wp-json/wplms/v1/reports';
		fetch(API_URL+'/top_competion_courses?\
			user_id='+this.props.user.user_id+'&role='+this.props.user.role+'&security='+this.props.user.security+'&number=10')
		.then(response => response.json())
      	.then(data => {
      		console.log('Fetched Data');
      		let total_count = 0;
      		data.map((item)=>{
      			total_count = total_count+item.count;
      		});
      		let labels = [];
      		let newdata=[];
      			console.log(total_count);
      		data.map((item)=>{
      			labels.push(item.course_name+ '- '+item.count);
      			console.log(Math.round(100*(item.count/total_count),2));
      			newdata.push(Math.round(100*(item.count/total_count),2))
      		});

      			console.log(newdata);
      		this.setState({data:newdata});
      		this.setState({labels});
      	});
	}
    render() {

    	let data = {
				labels: this.state.labels,
				datasets: [{
					data: this.state.data,
					backgroundColor: [
					'#FF6384',
					'#36A2EB',
					'#FFCE56',
					'#D2691E',
					'#33D800',
					'#9100D8'
					],
					hoverBackgroundColor: [
					'#FF6384',
					'#36A2EB',
					'#FFCE56',
					'#D2691E',
					'#33D800',
					'#9100D8'
					]
				}]
			};
        return (
            <div className="top_courses report_block">
              	<h3>{(window.wplms_reports_settings)?window.wplms_reports_settings.translations.top_courses_title:'Top Courses'}</h3>
              	<Doughnut data={data} />
            </div>
        );
    }
}

export default TopCourses;