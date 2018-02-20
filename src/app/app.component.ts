import { Component, ViewChild } from '@angular/core';
import { Nav, Platform, Events } from 'ionic-angular';
import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';

import { HomePage } from '../pages/home/home';
// import { ListPage } from '../pages/list/list';
import { LoginPage } from '../pages/login/login';
import { ContactsPage } from '../pages/contacts/contacts';
import { FavoritesPage } from '../pages/favorites/favorites';

import { GetDataProvider } from '../providers/get-data/get-data';
import { AuthServiceProvider } from '../providers/auth-service/auth-service';

import { Storage } from '@ionic/storage';
import { Camera, CameraOptions } from '@ionic-native/camera';

@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  @ViewChild(Nav) nav: Nav;

  rootPage: any = LoginPage;

  pages: Array<{title: string, component: any, icon: string}>;
  userInfo = [];
  place: string = '';
  userid:any = '0';

  constructor(public platform: Platform, public statusBar: StatusBar, public splashScreen: SplashScreen, private getDataService: GetDataProvider, private auth: AuthServiceProvider, public events: Events, private storage : Storage) {

    this.initializeApp();

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
        this.getUser(info[0]['userid']);
        // console.log(info[0]['userid']);
      }else{
        events.subscribe('user:created', (user, time) => {
          this.getUser(user);
        });
      }
    });

    // used for an example of ngFor and navigation
    this.pages = [
      { title: 'Dashboard', component: HomePage, icon: 'home' },
      { title: 'Contacts', component: ContactsPage, icon: 'contact' },
      { title: 'Favorites', component: FavoritesPage, icon: 'heart' },
    ];

  }

  initializeApp() {
    this.platform.ready().then(() => {
      // Okay, so the platform is ready and our plugins are available.
      // Here you can do any higher level native things you might need.
      this.statusBar.styleDefault();
      this.splashScreen.hide();
    });
  }

  getUser(userid){
    // if(userid != undefined){
      // console.log(userid);
      this.getDataService.getData('http://ibugg2.vmcgraphics.com/api/users/?id='+userid).subscribe(
        data => {
          this.userInfo = data;

          if(this.userInfo[0].city != '' && this.userInfo[0].country != ''){
            this.place = this.userInfo[0].city+', '+this.userInfo[0].country;
          }else if(this.userInfo[0].city != '' && this.userInfo[0].country == ''){
            this.place = this.userInfo[0].city;
          }else if(this.userInfo[0].country != '' && this.userInfo[0].city == ''){
            this.place = this.userInfo[0].country;
          }else{
            this.place = this.userInfo[0].username;
          }
        }
      );
    // }
  }

  openPage(page) {
    // Reset the content nav to have just this page
    // we wouldn't want the back button to show in this scenario
    this.nav.setRoot(page.component);
  }

  public logout() {
    this.auth.logout().subscribe(succ => {
      this.nav.setRoot('LoginPage')
    });
  }

}
