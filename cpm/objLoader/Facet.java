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

public class Facet {

	public Vector indexes;
	public Vector tindexes;
	public Vector nindexes;

	public Facet() {
		indexes = new Vector();
		tindexes = new Vector();
		nindexes = new Vector();
	}

	public int getSize(){
		return indexes.size();
	}
	public int getVertexIndex(int i){
		return ((Integer)indexes.elementAt(i)).intValue();
	}
	public int getTextureIndex(int i){
		return ((Integer)tindexes.elementAt(i)).intValue();		
	}
	public int getNormalIndex(int i){
		return ((Integer)nindexes.elementAt(i)).intValue();
	}
}
