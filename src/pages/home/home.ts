import { Component } from '@angular/core';
import { NavController } from 'ionic-angular';

import { GetDataProvider } from '../../providers/get-data/get-data';
import { AuthServiceProvider } from '../../providers/auth-service/auth-service';

import { Storage } from '@ionic/storage';

@Component({
	selector: 'page-home',
	templateUrl: 'home.html'
})
export class HomePage {
	stats:any = [];
	lastTen:any = [];

	constructor(public navCtrl: NavController, private getDataService: GetDataProvider, private auth: AuthServiceProvider, private storage : Storage) {
		this.auth.showLoading('Please Wait..');

		let info:any;
		this.storage.get('userid').then((val) => {
			// info = val;
			if(val != undefined){
				// console.log(val);
				info = [{
					auth : true,
					userid : val
				}];
			}else{
				info = this.auth.getUserInfo();
			}

			if(info != undefined && info[0]['auth'] == true){
				// this.navCtrl.setRoot('HomePage')
				this.auth.closeLoading();
				this.getStats(info[0]['userid']);
				this.getLastTen(info[0]['userid']);
				// console.log(info[0]['userid']);
			}else{
				this.navCtrl.setRoot('LoginPage')
				this.auth.closeLoading();
			}
		});
	}

	getStats(userid){
		this.getDataService.getData('http://ibugg2.vmcgraphics.com/api/stats/?userid='+userid+'').subscribe(
			data => this.stats = data
		);
	}

	getLastTen(userid){
		this.getDataService.getData('http://ibugg2.vmcgraphics.com/api/contacts/?userid='+userid+'&status=1&limit=10').subscribe(
			data => this.lastTen = data
		);
	}

}
