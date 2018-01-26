import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import 'rxjs/add/operator/map';
import 'rxjs/add/operator/do';
import 'rxjs/add/operator/catch';

import { Observable } from 'rxjs/Observable';
// import 'rxjs/observable';
import 'rxjs/add/observable/throw';

/*
  Generated class for the GetDataProvider provider.

  See https://angular.io/guide/dependency-injection for more info on providers
  and Angular DI.
*/
@Injectable()
export class GetDataProvider {


  constructor(private http: Http) {
    // console.log('Hello GetDataProvider Provider');
  }

  getData(url){
  	return this.http.get(url)
  	.do(this.logResponse)
  	.map(this.extractData)
    .do(this.logResponse)
  }

  // private catchError(error: Response | any){
  	// console.log(error);
  	// return Observable.throw(error.json().error || "Server Error");
  // }

  private logResponse(res: Response){
  	// console.log(res);
  }

  private extractData(res: Response){
  	return res.json();
  }

}
