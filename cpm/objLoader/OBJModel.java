/*
 * Alias .obj loader for processing
 * programmed by Tatsuya SAITO / UCLA Design | Media Arts 
 * Created on 2005/04/17
 *
 * 
 * Gunnar added functions:
 * public void setFadeMidPoint(float[] p_FadeMidPoint) 
 * public float[] getFadeMidPoint()
 * public void setFadeDistance(float p_fadeDistance)
 * public void setFadeSpace(float p_fadeSpace)
 * public void enableZFade(boolean p_fadeFlag)
 *  
 */

package objLoader;

import java.awt.event.KeyEvent;
import java.awt.event.MouseEvent;
import java.io.BufferedReader;
//import java.io.File;
//import java.io.FileReader;
//import java.io.IOException;
import java.io.InputStreamReader;
import java.util.zip.*;

//import java.net.MalformedURLException;
import java.net.URL;
import java.util.Hashtable;
import java.util.Vector;


import processing.core.PApplet;
import processing.core.PImage;
import CampusMap.Color;
import CampusMap.ObjectOfInterest;

/**
 * 
 * @author tatsuyas
 * 
 * TODO:
 * 
 */

public class OBJModel {

	Vector vertices; // vertexes
	Vector groups;
	Vector objects;
	Vector texturev; // texture coordinates
	Vector normv;
	
	float verticesArray[];
	float texturevArray[];
	float normvArray[];
	
	Hashtable materials;
	PApplet parent;
	ObjectOfInterest myWrap;
	int lod;
	PImage texture; // texture image
	int mode = PApplet.POLYGON; // render mode (ex. POLYGON, POINTS ..)
	boolean flagTexture = true;
	String mtlfilename = null;
	String myFileName;
	boolean bDebug = false;
	String sourceFolderURL;
	
	// Fade variables
	boolean fadeFlag = false;
	float[] fadeMidPoint;
	float fadeDistance=500;
	float fadeSpace=1000;
	
	//Line Variables
	boolean drawLines = false;
	Color	lineColor;

	public OBJModel(PApplet parent, ObjectOfInterest myWrap_p, int lod_p) {
		this.parent = parent;
		myWrap = myWrap_p;
		lod=lod_p;
		vertices = new Vector();
		texturev = new Vector();
		normv = new Vector();
		groups = new Vector();
		objects = new Vector();
		materials = new Hashtable();
		sourceFolderURL = new String("");
		lineColor = new Color(100,100,100);
	}
	
	public void setParentApplet(PApplet p_parent){
		this.parent = p_parent;
		parent.registerDispose(this);
	}

	public void pre() {
		// do something cool
	}

	public void draw(float matAlphaMultiplier, boolean greyed_p) {
		drawModel(matAlphaMultiplier, greyed_p);
	}

	public void showModelInfo() {

		debug("Total:");
		if (vertices == null)
			debug("\tV  Size: " + verticesArray.length);
		else
			debug("\tV  Size: " + vertices.size());
		if (texturev == null)
			debug("\tVt Size: " + texturevArray.length);
		else
			debug("\tVt Size: " + texturev.size());
		if (normv == null)
			debug("\tVn Size: " + normvArray.length);
		else
			debug("\tVn Size: " + normv.size());
		debug("\tG  Size: " + groups.size());

		for (int g = 0; g < groups.size(); g++) {
			Group tmpgroup = (Group) (groups.elementAt(g));
			debug("Group Name: " + tmpgroup.groupName);
			debug("\tMtl: " + tmpgroup.mtlName);
			debug("\tFacet Size: " + tmpgroup.facets.size());

		}
	}

	public void disableTexture() {
		flagTexture = false;
	}

	public void enableTexture() {
		flagTexture = true;
	}

	public void texture(PImage tex) {
		texture = tex;
	}
	
	/**
	 * Methods for fading comunication
	 * 
	 */
	public void setFadeMidPoint(float[] p_FadeMidPoint){
		fadeMidPoint=p_FadeMidPoint;
	} 
	public float[] getFadeMidPoint(){
		return fadeMidPoint;
	}
	public void setZFade(boolean p_fadeFlag){
		fadeFlag=p_fadeFlag;
	}
	public boolean getZFade(){
		return fadeFlag;
	}
	public void setFadeDistance(float p_fadeDistance){
		fadeDistance=p_fadeDistance;
	}
	public void setFadeSpace(float p_fadeSpace){
		fadeSpace=p_fadeSpace;
	}
	 
	public void setLineDrawing(boolean p_drawLines){
		drawLines=p_drawLines;
	}
	public boolean getLineDrawing(){
		return drawLines;
	}
	public void setLineColor(Color p_lineColor){
		lineColor = p_lineColor;
	}

	public void drawModel(float matAlphaMultiplier, boolean greyed) {
		float alphaMultiplier = 1;
		try {
			int vtidx = 0, vnidx = 0, vidx = 0, vidx2=0;
			PImage imgtex = null;
			boolean bTexture = false;
			// render all triangles
			for (int g = 0; g < groups.size(); g++) {
				Group tmpgroup = (Group) (groups.elementAt(g));
				Material mtl = null;
				float[] colors = {255,255,255,255};
				// debug("hogehoge1");
				if (tmpgroup.mtlName != null) {
					try{
						mtl = (Material) materials.get(tmpgroup.mtlName);
						//if(tmpgroup.mtlName=="Backstei")System.out.println("mtl: "+mtl);
						colors[0]=255.0f * mtl.Ka[0];
						colors[1]=255.0f * mtl.Ka[1];
						colors[2]=255.0f * mtl.Ka[2];
						colors[3]=255.0f * mtl.d * matAlphaMultiplier;
					} catch(NullPointerException e){
						System.out.println("material: "+tmpgroup.mtlName);
					}
				}
				if(!greyed)parent.fill(colors[0],colors[1],colors[2],colors[3]);
				else{
					parent.fill( (colors[0]+colors[1]+colors[2]) /3);
				}
				// debug("hogehoge2");
				// check texture availability
				if (texture != null) {
					bTexture = true;
				} else if (mtl != null) {
					if (mtl.map_Kd != null) {
						bTexture = true;
						imgtex = mtl.map_Kd;
					} else {
						bTexture = false;
					}
				}
				// debug("hogehoge3");
				if (tmpgroup.facets.size() > 0) {
					for (int f = 0; f < tmpgroup.facets.size(); f++) {

						Facet tmpf = (Facet) (tmpgroup.facets.elementAt(f));

						//parent.textureMode(PApplet.NORMALIZED);
						parent.beginShape(mode); // specify render mode

						if (bTexture && flagTexture) {
							if (tmpf.intTIndexes.length > 0) {
//								int minValX;
//								int maxValX;
//								int minValY;
//								int maxValY;
//								for(int patternIndex=0;patternIndex<tmpf.tindexes.size();patternIndex++){
//									//minValX = tmpf.tindexes.get(patternIndex);
//								}
//									
								if (texture != null)
									parent.texture(texture); // setting
								// texture
								else if (imgtex != null)
									parent.texture(imgtex); // setting texture
							} else {
								bTexture = false;
							}
						} else {
							bTexture = false;
						}

/**
 *  {{added by Gunnar
 
						// z-fading enabled?
						if(fadeFlag==true){
							if(fadeMidPoint==null)
								parent.die("Sorry, you have to call at least once the function to set the midPoint.");
							else if(fadeMidPoint.length!=3)
								parent.die("You did't set a 3-dimensional information for the middle-Point");
							else {
								float[] collectVector = new float[tmpf.indexes.size()];
								float mediaDistance=0;
								for(int collVertexIndex=0;collVertexIndex<tmpf.indexes.size();collVertexIndex++){
									vidx = ((Integer) (tmpf.indexes.elementAt(collVertexIndex))).intValue();
									v = (Vertex) vertexes.elementAt(vidx - 1);
									float[] distanceVertex = new float[3]; 
									distanceVertex[0]=v.vx>fadeMidPoint[0]?v.vx-fadeMidPoint[0]:fadeMidPoint[0]-v.vx;
									distanceVertex[1]=v.vy>fadeMidPoint[1]?v.vy-fadeMidPoint[1]:fadeMidPoint[1]-v.vy;
									distanceVertex[2]=v.vz>fadeMidPoint[2]?v.vz-fadeMidPoint[2]:fadeMidPoint[2]-v.vz;
									collectVector[collVertexIndex] 			= (float)Math.sqrt((distanceVertex[0]*distanceVertex[0])+(distanceVertex[1]*distanceVertex[1])+(distanceVertex[2]*distanceVertex[2]));
									mediaDistance+=collectVector[collVertexIndex];
									if(f==10)debug("collectVector: "+collectVector[collVertexIndex]);
								}
								if(f==10)debug("A Face: ");
								mediaDistance=mediaDistance/(collectVector.length);
								if(f==10)debug("mediaDistance: "+mediaDistance);
								alphaMultiplier = 0;
								if(mediaDistance<fadeDistance)alphaMultiplier = 1;
								else if(mediaDistance<fadeDistance+fadeSpace){
									alphaMultiplier = ((mediaDistance-fadeDistance)/(float)fadeSpace); 
									alphaMultiplier=alphaMultiplier<0?1:(alphaMultiplier>1?0:1-alphaMultiplier);
								} 
								if(f==10){
									debug("alphaMultiplier: "+alphaMultiplier);
									parent.fill(255,0,0,colors[3]*alphaMultiplier);
								}else{ 
									float[] fadeColor = {167,198,167};
									float[] colorBuffer = new float[3];
									for(int u=0;u<3;u++)
										colorBuffer[u]=(float)(colors[u]+(255-colors[u])*(1.0-alphaMultiplier));
										colorBuffer[u]=(float)(colors[u]+(fadeColor[u]-colors[u])*(1.0-alphaMultiplier));
									parent.fill(colorBuffer[0],colorBuffer[1],colorBuffer[2],colors[3]);
									parent.noStroke();
								//}
							}
						}
*/						if(alphaMultiplier!=0){
/**
*  }}added by Gunnar
*/

							if (tmpf.intIndexes.length > 0) {
								for (int fp = 0; fp < tmpf.intIndexes.length; fp++) {
	
									vidx = tmpf.intIndexes[fp];
									
									//v = (Vertex) vertices.elementAt(vidx - 1);
									//if (v != null) {
									int vIdx = (vidx - 1)*3;
										try {
											if (tmpf.intNIndexes.length > 0) {
												vnidx = tmpf.intNIndexes[fp];
												int nIdx = (vnidx - 1)*3;
												parent.normal(-normvArray[nIdx],
														-normvArray[nIdx+1],
														-normvArray[nIdx+2]);
											}
	
											if (bTexture) {
	
												vtidx = tmpf.intTIndexes[fp];
	
												int tIdx = (vtidx - 1)*2;
												
												parent.vertex(-verticesArray[vIdx],
														verticesArray[vIdx+1],
														verticesArray[vIdx+2],
														1.0f - texturevArray[tIdx], texturevArray[tIdx+1]);
											} else
												parent.vertex(-verticesArray[vIdx],
														verticesArray[vIdx+1],
														verticesArray[vIdx+2]);
										} catch (Exception e) {
											e.printStackTrace();
										}
									//} else {
									//	parent.vertex(-v.vx, v.vy, v.vz);
									//}
	
								}
							}
							parent.endShape();
	// Same procedure to draw just the lines in question
							if(drawLines){
								parent.noFill();
								parent.stroke(lineColor.r, lineColor.g, lineColor.b, 255*matAlphaMultiplier);
								if (tmpf.intIndexes.length > 0) {
									for (int fp = 0; fp < tmpf.intIndexes.length; fp++) {
		
										vidx = tmpf.intIndexes[fp];
										if(fp<tmpf.intIndexes.length-1)
											vidx2 = tmpf.intIndexes[fp+1];
										else
											vidx2 = tmpf.intIndexes[0];
										
										int vIdx = (vidx - 1)*3;
										int eIdx = (vidx2 - 1)*3;
										if (	verticesArray[vIdx+0]==verticesArray[eIdx+0]
										    &&	verticesArray[vIdx+2]==verticesArray[eIdx+2]
										    ||	verticesArray[vIdx+2]==verticesArray[eIdx+2]
										    &&	verticesArray[vIdx+1]==verticesArray[eIdx+1]
										    ||	verticesArray[vIdx+0]==verticesArray[eIdx+0]
										    &&	verticesArray[vIdx+1]==verticesArray[eIdx+1])
											parent.line(-verticesArray[vIdx+0],
													verticesArray[vIdx+1],
													verticesArray[vIdx+2],
													-verticesArray[eIdx+0],
													verticesArray[eIdx+1],
													verticesArray[eIdx+2]);
									}
								}
								parent.endShape();
								parent.fill(colors[0],colors[1],colors[2],colors[3]);
								parent.noStroke();
							}
	// End Linedrawing
						}// 
					}
				}
			}
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public void drawMode(int mode) {
		this.mode = mode;
	}

	public BufferedReader getBufferedReader(String filename) throws Exception {

		BufferedReader retval = null;
		URL url = null;

		if (filename.startsWith("http://")) {
			//System.out.println("loading file from url: " + filename);
			url = new URL(filename);
			//support for gunzip
			if (filename.endsWith(".gz")) {
				retval = new BufferedReader(
							new InputStreamReader(
									new GZIPInputStream(
											url.openStream())));
			} else {
				retval = new BufferedReader(new InputStreamReader(url
						.openStream()));
			}
		}
		else {
			System.out.println("loading file from hd: " + filename);
			return new BufferedReader(new InputStreamReader(parent.getClass().getResourceAsStream(filename)));
		}
		return retval;
		
//		Original Error handling
//		parent.die("Could not find .OBJ file " + filename, null);

	}

	//public void load(InputStream filename) {
	public void load(String url, String filename) {
		myFileName=filename;
		sourceFolderURL = url;
		try {
			BufferedReader readObject = getBufferedReader(sourceFolderURL + filename);
			parseOBJ(readObject);
			readObject.close();
			//parseOBJ(new BufferedReader(new InputStreamReader(filename)));
			if (mtlfilename != null)
				//parseMTL(new BufferedReader(new InputStreamReader(parent.getClass().getResourceAsStream("data/"+mtlfilename))));
				parseMTL(getBufferedReader(sourceFolderURL + mtlfilename));
			makeThingsSmaller();
			debug("model loaded");
			myWrap.setLodModelLoaded(lod);
		} catch (Exception e) {
			e.printStackTrace();
			myWrap.setLodModelLoadFailed(lod);
		}
	}

	public void parseOBJ(BufferedReader bread) throws Exception {
		String line;
		Group currentGroup = null;
		int ngCounter = 0;
		while ((line = bread.readLine()) != null) {
//				parseCounter++;
//				System.out.println("parseCounter: "+parseCounter);
//				// debug(line);
			// parse the line
			String[] elements = line.split("\\s+");
			if (elements.length > 0) {
				// analyze the format
				if (elements[0].equals("v")) {
					Vertex tmpv = new Vertex();
					tmpv.vx = Float.valueOf(elements[1]).floatValue();
					tmpv.vy = Float.valueOf(elements[2]).floatValue();
					tmpv.vz = Float.valueOf(elements[3]).floatValue();
					vertices.add(tmpv);
				} else if (elements[0].equals("vn")) {
					Vertex tmpv = new Vertex();
					tmpv.vx = Float.valueOf(elements[1]).floatValue();
					tmpv.vy = Float.valueOf(elements[2]).floatValue();
					tmpv.vz = Float.valueOf(elements[3]).floatValue();
					normv.add(tmpv);
				} else if (elements[0].equals("g")
						|| elements[0].equals("o")) {
					Group tmpG = new Group();
					currentGroup = tmpG;
					if (elements.length <= 1) {
						currentGroup.groupName = "tmp_named_" + ngCounter;
						ngCounter++;
					} else {
						currentGroup.groupName = elements[1];
					}
					groups.add(currentGroup);
				} else if (elements[0].equals("usemtl")) {
					currentGroup = new Group();
					currentGroup.groupName = "tmp_named_" + ngCounter++;
					currentGroup.mtlName = elements[1];
					groups.add(currentGroup);
				} else if (elements[0].equals("f")) {
					Facet tmpf = new Facet();
					if (elements.length < 3) {
						debug("Warning: potential model data error");
					}
					for (int i = 1; i < elements.length; i++) {
						String seg = elements[i];
						if (seg.indexOf("/") > 0) {
							String[] forder = seg.split("/");

							if (forder.length > 2) {
								if (forder[2].length() > 0) {
									int tmpVal = Integer.valueOf(forder[2])
											.intValue();
									if (tmpVal < 0) {
										tmpf.nindexes.add(new Integer(
												vertices.size() + tmpVal +1));
									} else {
										tmpf.nindexes.add(new Integer(
												tmpVal));
									}
								}
								if (forder[1].length() > 0) {
									int tmpVal = Integer.valueOf(forder[1])
											.intValue();
									if (tmpVal < 0) {
										tmpf.tindexes.add(new Integer(
												vertices.size() + tmpVal +1));
									} else {
										tmpf.tindexes.add(new Integer(
												tmpVal));
									}
								}
								if (forder[0].length() > 0) {
									int tmpVal = Integer.valueOf(forder[0])
											.intValue();
									if (tmpVal < 0) {
										tmpf.indexes.add(new Integer(
												vertices.size() + tmpVal +1));
									} else {
										tmpf.indexes
												.add(new Integer(tmpVal));
									}
								}
							} else if (forder.length > 1) {
								if (forder[1].length() > 0) {
									int tmpVal = Integer.valueOf(forder[1])
											.intValue();
									if (tmpVal < 0) {
										tmpf.tindexes.add(new Integer(
												vertices.size() + tmpVal + 1));
									} else {
										tmpf.tindexes.add(new Integer(
												tmpVal));
									}
								}
								if (forder[0].length() > 0) {
									int tmpVal = Integer.valueOf(forder[0])
											.intValue();
									if (tmpVal < 0) {
										tmpf.indexes.add(new Integer(
												vertices.size() + tmpVal +1));
									} else {
										tmpf.indexes
												.add(new Integer(tmpVal));
									}
								}
							} else if (forder.length > 0) {
								if (forder[0].length() > 0) {
									int tmpVal = Integer.valueOf(forder[0])
											.intValue();
									if (tmpVal < 0) {
										tmpf.indexes.add(new Integer(
												vertices.size() + tmpVal + 1));
									} else {
										tmpf.indexes
												.add(new Integer(tmpVal));
									}
								}
							}
						} else {
							if (seg.length() > 0)
								tmpf.indexes.add(Integer.valueOf(seg));
						}
					}
					if (currentGroup != null)
						currentGroup.facets.add(tmpf);

				} else if (elements[0].equals("vt")) {
					Vertex tmpv = new Vertex();
					tmpv.vx = Float.valueOf(elements[1]).floatValue();
					// avoid negative values
					//tmpv.vx = (tmpv.vx<0?-tmpv.vx:tmpv.vx);
					tmpv.vy = Float.valueOf(elements[2]).floatValue();
					texturev.add(tmpv);
				} else if (elements[0].equals("mtllib")) {
					mtlfilename = elements[1];
					while (mtlfilename.startsWith("/") || mtlfilename.startsWith(".")){
						mtlfilename = mtlfilename.substring(1,mtlfilename.length());
					}
				}
			}
		}
	}
	
	public void makeThingsSmaller() {
		for (int g = 0; g < groups.size(); g++) {
			Group tmpgroup = (Group) (groups.elementAt(g));
			for (int f = 0; f < tmpgroup.facets.size(); f++) {
				Facet tmpfacet = (Facet) (tmpgroup.facets.elementAt(f));
				
				int numberOfIntegers = tmpfacet.indexes.size();
				tmpfacet.intIndexes = new int[numberOfIntegers];
				for (int i = 0; i < numberOfIntegers; i++) {
					tmpfacet.intIndexes[i] = ((Integer)tmpfacet.indexes.elementAt(i)).intValue();
				}
				tmpfacet.indexes.clear();
				tmpfacet.indexes = null;
				
				numberOfIntegers = tmpfacet.tindexes.size();
				tmpfacet.intTIndexes = new int[numberOfIntegers];
				for (int i = 0; i < numberOfIntegers; i++) {
					tmpfacet.intTIndexes[i] = ((Integer)tmpfacet.tindexes.elementAt(i)).intValue();
				}
				tmpfacet.tindexes.clear();
				tmpfacet.tindexes = null;
				
				numberOfIntegers = tmpfacet.nindexes.size();
				tmpfacet.intNIndexes = new int[numberOfIntegers];
				for (int i = 0; i < numberOfIntegers; i++) {
					tmpfacet.intNIndexes[i] = ((Integer)tmpfacet.nindexes.elementAt(i)).intValue();
				}
				tmpfacet.nindexes.clear();
				tmpfacet.nindexes = null;
			}
		}
		int numberOfVertices = vertices.size();
		Vertex tmpVertex;
		verticesArray = new float[numberOfVertices*3];
		for (int v = 0; v < vertices.size(); v++) {
			tmpVertex = (Vertex) (vertices.elementAt(v));
			verticesArray[v*3 + 0] = tmpVertex.vx;
			verticesArray[v*3 + 1] = tmpVertex.vy;
			verticesArray[v*3 + 2] = tmpVertex.vz;
		}
		vertices.clear();
		vertices = null;
		
		numberOfVertices = this.normv.size();
		normvArray = new float[numberOfVertices*3];
		for (int v = 0; v < normv.size(); v++) {
			tmpVertex = (Vertex) (normv.elementAt(v));
			normvArray[v*3 + 0] = tmpVertex.vx;
			normvArray[v*3 + 1] = tmpVertex.vy;
			normvArray[v*3 + 2] = tmpVertex.vz;
		}
		normv.clear();
		normv = null;

		numberOfVertices = this.texturev.size();
		texturevArray = new float[numberOfVertices*2];
		for (int v = 0; v < texturev.size(); v++) {
			tmpVertex = (Vertex) (texturev.elementAt(v));
			texturevArray[v*2 + 0] = tmpVertex.vx;
			texturevArray[v*2 + 1] = tmpVertex.vy;
		}
		texturev.clear();
		texturev = null;
		
	}

	public void parseMTL(BufferedReader bread) {
		try {
			String line;
			Material currentMtl = null;
			while ((line = bread.readLine()) != null) {
				// parse the line
				String elements[] = line.split("\\s+");
				debug(line);
				if (elements.length > 0) {
					// analyze the format

					if (elements[0].equals("newmtl")) {
						debug("material: " + elements[1]);
						String mtlName = elements[1];
						Material tmpMtl = new Material();
						currentMtl = tmpMtl;
						materials.put(mtlName, tmpMtl);
					} else if (elements[0].equals("map_Ka")
							&& elements.length > 1) {
						debug("texture ambient: " + elements[1]);
						//String texname = elements[1];
						// currentMtl.map_Ka = parent.loadImage(texname);
					} else if (elements[0].equals("map_Kd")
							&& elements.length > 1) {
						debug("texture diffuse: " + elements[1]);
						String texname = elements[1];
						currentMtl.map_Kd = parent.loadImage(sourceFolderURL+texname);
					} else if (elements[0].equals("Ka") && elements.length > 1) {
						debug("material ambient: " + elements[1] + ", "
								+ elements[2] + ", " + elements[3]);
						currentMtl.Ka[0] = Float.valueOf(elements[1])
								.floatValue();
						currentMtl.Ka[1] = Float.valueOf(elements[2])
								.floatValue();
						currentMtl.Ka[2] = Float.valueOf(elements[3])
								.floatValue();

					} else if (elements[0].equals("d") && elements.length > 1) {
						debug("material alpha: " + elements[1]);
						currentMtl.d = Float.valueOf(elements[1]).floatValue();

					}
				}
			}
		} catch (Exception e) {
			System.out.println("filename/mtlfilename: "+myFileName+"/"+mtlfilename);
			e.printStackTrace();
		}
	}

	/* Functions for addressing each group/facet/vertex */

	public int getGroupsize() {
		return this.groups.size();
	}

	public Group getGroup(int i) {
		return (Group) this.groups.elementAt(i);
	}

	public int getVertexsize() {
		return this.vertices.size();
	}

	public Vertex getVertex(int i) {
		return (Vertex) vertices.elementAt(i);
	}

	public void setVertex(int i, Vertex vertex) {
		Vertex tmpv = (Vertex) vertices.elementAt(i);
		tmpv.vx = vertex.vx;
		tmpv.vy = vertex.vy;
		tmpv.vz = vertex.vz;
	}

	/* Functions for for debugging */

	public void debugMode() {
		bDebug = true;
	}

	public void debug(String message) {
		if (bDebug)
			System.out.println(message);
	}
	public void size(int w, int h) {

	}

	public void post() {
	}

	public void mouse(MouseEvent event) {
	}

	public void key(KeyEvent e) {
	}

	public void dispose() {
	}


}
