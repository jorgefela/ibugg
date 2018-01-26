	import { Component } from '@angular/core';
	import { NavController, AlertController, IonicPage } from 'ionic-angular';
	import { AuthServiceProvider } from '../../providers/auth-service/auth-service';
	
	import { HomePage } from '../home/home';

	import { Storage } from '@ionic/storage';


	/**
	 * Generated class for the LoginPage page.
	 *
	 * See https://ionicframework.com/docs/components/#navigation for more info on
	 * Ionic pages and navigation.
	 */

	@IonicPage()
	@Component({
		selector: 'page-login',
		templateUrl: 'login.html',
	})

	export class LoginPage {

		registerCredentials:any = { email: '', password: '', usertype: '' };
		userid = '';

		constructor(public nav: NavController, private auth: AuthServiceProvider, private alertCtrl: AlertController, private storage: Storage) {
			this.auth.showLoading('Please Wait..');

    		let info:any;
    		// let userid_form_storage: any;

    		this.storage.get('userid').then((val) => {
    			// info = val;
    			if(val != undefined){
    				console.log(val);
					info = [{
						auth : true,
						userid : val
					}];
    			}else{
		    		info = this.auth.getUserInfo();
		    	}

	    		if(info != undefined && info[0]['auth'] == true){
					this.nav.setRoot(HomePage);
					this.auth.closeLoading();
	    			console.log(info);
	    		}else{
					this.auth.closeLoading();
	    		}
    		});

		}


		public createAccount() {
			this.nav.push('RegisterPage');
		}

		public login() {
			this.auth.showLoading();
			this.auth.login(this.registerCredentials).subscribe(allowed => {
				if (allowed) {        
					this.nav.setRoot(HomePage);
				} else {
					this.showError("Access Denied. Try Again.");
				}
				this.auth.closeLoading();
			},
			error => {
				this.showError(error);
			});
		}

		showError(text) {
			this.auth.closeLoading();

			let alert = this.alertCtrl.create({
				title: 'Fail',
				subTitle: text,
				buttons: ['OK']
			});
			alert.present(prompt);
		}

	}
