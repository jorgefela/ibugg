import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';

import { GetDataProvider } from '../../providers/get-data/get-data';
import { Storage } from '@ionic/storage';

/**
 * Generated class for the EditContactsPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-edit-contacts',
  templateUrl: 'edit-contacts.html',
})
export class EditContactsPage {
	editInfo: any = [];
	image: string = '';

  constructor(public navCtrl: NavController, public navParams: NavParams, public storage: Storage, private getDataService: GetDataProvider) {
  	this.storage.get('edit-id').then(user => {
  		// this.id = user;
  		// console.log(user);
  		this.get_info(user);
  	});

  }

  get_info(id){
	this.getDataService.getData('http://ibugg2.vmcgraphics.com/api/contacts/?id='+id+'').subscribe(
		data => {
			this.editInfo = data
			console.log(data);
		}
	);
  }

}
