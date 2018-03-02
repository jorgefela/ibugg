import { Component } from '@angular/core';
import { IonicPage, NavController, ActionSheetController, ToastController, Platform, LoadingController, Loading } from 'ionic-angular';

import { GetDataProvider } from '../../providers/get-data/get-data';

import { ContactsPage } from '../contacts/contacts';

import { File } from '@ionic-native/file';
import { Transfer, TransferObject } from '@ionic-native/transfer';
import { FilePath } from '@ionic-native/file-path';
import { Camera } from '@ionic-native/camera';

/**
 * Generated class for the NewContactPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-new-contact',
  templateUrl: 'new-contact.html',
})
export class NewContactPage {
  	public response: any;
	lastImage: string = null;
	loading: Loading;

	bCard: any = null;
	bName: string = null;
	bPhone: string = null;
	bEmail: string = null;
	bWeb: string = null;
	bAddress: string = null;
	bCompany: string = null;
	bJob: string = null;

	constructor(public navCtrl: NavController, private camera: Camera, private transfer: Transfer, private file: File, private filePath: FilePath, public actionSheetCtrl: ActionSheetController, public toastCtrl: ToastController, public platform: Platform, public loadingCtrl: LoadingController) { }

	public presentActionSheet() {
		let actionSheet = this.actionSheetCtrl.create({
			title: 'Select Image Source',
			buttons: [
				{
					text: 'Load from Library',
					handler: () => {
						this.takePicture(this.camera.PictureSourceType.PHOTOLIBRARY);
					}
				},
				{
					text: 'Use Camera',
					handler: () => {
						this.takePicture(this.camera.PictureSourceType.CAMERA);
					}
				},
				{
					text: 'Cancel',
					role: 'cancel'
				}
			]
		});
		actionSheet.present();
	}

	openPage(page) {
		// Reset the content nav to have just this page
		// we wouldn't want the back button to show in this scenario
		this.navCtrl.setRoot(page);
	}

	public takePicture(sourceType) {
		this.loading = this.loadingCtrl.create({
			content: 'Wait...',
		});
		this.loading.present();

	  // Create options for the Camera Dialog
	  var options = {
	    quality: 100,
	    sourceType: sourceType,
	    saveToPhotoAlbum: false,
	    correctOrientation: true
	  };
	 
	  // Get the data of an image
	  this.camera.getPicture(options).then((imagePath) => {
	    // Special handling for Android library
	    if (this.platform.is('android') && sourceType === this.camera.PictureSourceType.PHOTOLIBRARY) {
	      this.filePath.resolveNativePath(imagePath)
	        .then(filePath => {
	          let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
	          let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));
	          this.copyFileToLocalDir(correctPath, currentName, this.createFileName());
	        });
	    } else {
	      var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
	      var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);
	      this.copyFileToLocalDir(correctPath, currentName, this.createFileName());
	    }
		this.loading.dismissAll()
	  }, (err) => {
	    this.presentToast('Error while selecting image.');
		this.loading.dismissAll()
	  });
	}

	// Create a new name for the image
	private createFileName() {
		var d = new Date(),
		n = d.getTime(),
		newFileName =  n + ".jpg";
		return newFileName;
	}

	// Copy the image to a local folder
	private copyFileToLocalDir(namePath, currentName, newFileName) {
		this.file.copyFile(namePath, currentName, file.dataDirectory, newFileName).then(success => {
			this.lastImage = newFileName;
			this.uploadImage();
		}, error => {
			this.presentToast('Error while storing file.');
		});
	}

	private presentToast(text) {
		let toast = this.toastCtrl.create({
			message: text,
			duration: 3000,
			position: 'top'
		});
		toast.present();
	}

	// Always get the accurate path to your apps folder
	public pathForImage(img) {
		if (img === null) {
			return '';
		} else {
			return file.dataDirectory + img;
		}
	}

	public uploadImage() {
		// Destination URL
		var url = "http://ibugg2.vmcgraphics.com/test-ocr/_index.php";

		// File for Upload
		var targetPath = this.pathForImage(this.lastImage);

		// File name only
		var filename = this.lastImage;

		var options = {
			fileKey: "file",
			fileName: filename,
			chunkedMode: false,
			mimeType: "multipart/form-data",
			params : {'fileName': filename}
		};

		const fileTransfer: TransferObject = this.transfer.create();

		this.loading = this.loadingCtrl.create({
			content: 'Uploading...',
		});
		this.loading.present();

		// Use the FileTransfer to upload the image
		fileTransfer.upload(targetPath, url, options).then(data => {
			this.loading.dismissAll()
			this.presentToast('Image succesful uploaded.');
			// let bCard = data['response']['businessCard']['field'];
			let json = JSON.parse(data['response']);
			let fields = json['businessCard']['field'];

			this.bCard = fields;
			this.bName = fields;
			this.bPhone = fields;
			this.bEmail = fields;
			this.bWeb = fields;
			this.bAddress = fields;
			this.bCompany = fields;
			this.bJob = fields;
		}, err => {
			this.loading.dismissAll()
			this.presentToast('Error while uploading file.');
		});
	}



	
}