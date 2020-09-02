import React, { Component } from 'react';

import TopCourses from './TopCourses';
import TopCompletions from './TopCompletions';

class Reports extends Component {

    constructor(props){
        super(props);
        this.state={
            user:{
                    user_id:(window.wplms_reports_settings)?window.wplms_reports_settings.settings.user_id:1,
                    role:(window.wplms_reports_settings)?window.wplms_reports_settings.settings.role:'admin',
                    security:(window.wplms_reports_settings)?window.wplms_reports_settings.settings.security:1,
                },
            
        }
    }
    componentWillMount(){
     
    }
    render() {
        return (
            <div className="wplms_reports_sections">
                <div className="wplms_reports_sidebars">
                    <TopCourses user={this.state.user} />
                </div>  
                <div className="App">
                    <TopCompletions user={this.state.user}  />
                </div>  
            </div>
        );
    }
}

export default Reports;
