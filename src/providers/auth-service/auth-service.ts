// import { HttpClient } from '@angular/common/http';
import { LoadingController, Loading, Events } from 'ionic-angular';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/map';

import { GetDataProvider } from '../../providers/get-data/get-data';
import {Md5} from 'ts-md5/dist/md5';

import { Storage } from '@ionic/storage';

/*
  Generated class for the AuthServiceProvider provider.

  See https://angular.io/guide/dependency-injection for more info on providers
  and Angular DI.
*/

export class User {
  name: string;
  email: string;
 
  constructor(name: string, email: string) {
    this.name = name;
    this.email = email;
  }
}

@Injectable()
export class AuthServiceProvider {
  currentUser: any;
  loading: Loading;
  // auth: any = [];
  // access: any;

  constructor(private getDataService: GetDataProvider, private loadingCtrl: LoadingController, public events: Events, private storage: Storage) {  }

  public login(credentials) {
    if(credentials.usertype == null || credentials.usertype == undefined || credentials.usertype == ''){
      return Observable.throw("Please select an user type.");
    }else if (credentials.email === null || credentials.password === null || credentials.email === '' || credentials.password === '') {
      return Observable.throw("Please insert credentials");
    } else {
      return Observable.create(observer => {
        // At this point make a request to your backend to make a real check!
        let username: string = credentials.email;
        let usertype: any = credentials.usertype;
        let password: any = Md5.hashStr(credentials.password);
        let access : any = false;

        this.getDataService.getData('http://ibugg2.vmcgraphics.com/api/users/?auth=1&username='+username+'&password='+password+'&usertype='+usertype+'').subscribe(
          data => {
            if(data['auth'] == true){
              access = true;
            }else{
              access = false;
            }

            this.currentUser = [{
              auth : true,
              userid : data['userid'],
              usertype : data['usertype']
            }];
            
            this.events.publish('user:created', data['userid'], Date.now());
            this.storage.set('userid', data['userid']);
            this.storage.set('usertype', usertype);
            observer.next(access);
            observer.complete();
          }
        );

      });
    }
  }
 
  public register(credentials) {
    if (credentials.email === null || credentials.password === null) {
      return Observable.throw("Please insert credentials");
    } else {
      // At this point store the credentials to your backend!
      return Observable.create(observer => {
        observer.next(true);
        observer.complete();
      });
    }
  }
 
  public getUserInfo() : User {
    return this.currentUser;
  }
 
  public logout() {
    return Observable.create(observer => {
      this.currentUser = null;
      this.storage.remove('userid');
      observer.next(true);
      observer.complete();
    });
  }

  showLoading(content = '') {
    this.loading = this.loadingCtrl.create({
      content: content,
      // dismissOnPageChange: true
    });
    this.loading.present();
  }

  closeLoading(){
    this.loading.dismiss();
  }
}
