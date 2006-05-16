/*
 * Alias .obj loader for processing
 * programmed by Tatsuya SAITO / UCLA Design | Media Arts 
 * Created on 2005/04/17
 *
 * 
 *  
 */

package objLoader;

import java.util.Vector;

/**
 * @author tatsuyas
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */
public class Group {
	public Vector facets;
	public String mtlName;
	public String groupName;
	public Group(){
		facets = new Vector();
	}
	
	public String getName(){
		return groupName;
	}
	public String getMtlname(){
		return mtlName;
	}
	public int getFacetsize(){
		return facets.size();
	}
	public Facet getFacet(int i){
		return (Facet)facets.elementAt(i);
	}
}
