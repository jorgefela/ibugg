import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, Events } from 'ionic-angular';

import { GetDataProvider } from '../../providers/get-data/get-data';
import { AuthServiceProvider } from '../../providers/auth-service/auth-service';
import { EditContactsPage } from '../edit-contacts/edit-contacts';
import { NewContactPage } from '../new-contact/new-contact';

import { Storage } from '@ionic/storage';
import { Camera, CameraOptions } from '@ionic-native/camera';

/**
 * Generated class for the ContactsPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

 @IonicPage()
 @Component({
 	selector: 'page-contacts',
 	templateUrl: 'contacts.html',
 })
 export class ContactsPage {

 	contacts: any = [];
 	username = '';
 	email = '';


 	constructor(public navCtrl: NavController, private getDataService: GetDataProvider, private auth: AuthServiceProvider, private storage : Storage, public events: Events, private camera: Camera) {
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
				this.getContacts(info[0]['userid']);
				// console.log(info[0]['userid']);
			}else{
				this.navCtrl.setRoot('LoginPage')
				this.auth.closeLoading();
			}
		});

 	}

 	public getImage(){
	  const options: CameraOptions = {
	    quality: 100,
	    destinationType: this.camera.DestinationType.DATA_URL,
	    encodingType: this.camera.EncodingType.JPEG,
	    mediaType: this.camera.MediaType.PICTURE
	  }

	  this.camera.getPicture(options).then((imageData) => {
	   // imageData is either a base64 encoded string or a file URI
	   // If it's base64:
	   let base64Image = 'data:image/jpeg;base64,' + imageData;
	   
	  }, (err) => {
	   // Handle error
	  });
  	}


 	getContacts(userid){
 		this.getDataService.getData('http://ibugg2.vmcgraphics.com/api/contacts/?userid='+userid+'&status=1').subscribe(
 			data => this.contacts = data
 			);
 	}

 	changeStatus(id, column, value){
 		this.getDataService.getData('http://ibugg2.vmcgraphics.com/api/contacts/update/?id='+id+'&column='+column+'&value='+value+'').subscribe(
 			data => {
 				if(data.status == true){
 					this.navCtrl.setRoot(this.navCtrl.getActive().component);
 				}
 			}
 			);
 	}

 	public editContact(id){
       	this.storage.set('edit-id', id);
        this.navCtrl.setRoot('EditContactsPage');

 	}
 }
