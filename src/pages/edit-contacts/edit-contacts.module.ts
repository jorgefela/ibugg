import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { EditContactsPage } from './edit-contacts';

@NgModule({
  declarations: [
    EditContactsPage,
  ],
  imports: [
    IonicPageModule.forChild(EditContactsPage),
  ],
})
export class EditContactsPageModule {}
