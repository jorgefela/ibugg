import { NgModule } from '@angular/core';
import { Nav, IonicPageModule } from 'ionic-angular';
import { ContactsPage } from './contacts';



@NgModule({
  declarations: [
    ContactsPage,
  ],
  imports: [
    IonicPageModule.forChild(ContactsPage),
  ],
})
export class ContactsPageModule {}
